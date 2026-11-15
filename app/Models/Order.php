<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number', 'user_id', 'customer_name', 'customer_phone', 'customer_email',
    'delivery_zone_id', 'district', 'address', 'note',
    'subtotal', 'delivery_charge', 'total',
    'payment_method', 'payment_status', 'status', 'locale', 'paid_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Readable, hard-to-guess order number like BG-261115-7K3QD. Leaves out 0/O and 1/I
     * so customers can read it back over the phone.
     */
    public static function newOrderNumber(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $number = 'BG-'.now()->format('ymd').'-'.$suffix;
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Status changes from the admin panel. Each one only applies from the expected state
     * (a single conditional UPDATE), so a double click or two staff members can't skip steps.
     *
     * @return bool whether the order changed
     */
    public function markShipped(): bool
    {
        return $this->moveStatus(from: 'confirmed', to: 'shipped');
    }

    public function markDelivered(): bool
    {
        return $this->moveStatus(from: 'shipped', to: 'delivered');
    }

    /** Paid after auto-cancel (docs §5.8): record that the money was returned. */
    public function markRefunded(): bool
    {
        $changed = self::whereKey($this->id)
            ->where('status', 'cancelled')
            ->where('payment_status', 'paid')
            ->update(['payment_status' => 'refunded']) === 1;

        if ($changed) {
            $this->payments()->where('status', 'completed')->update(['status' => 'refunded']);
            $this->refresh();
        }

        return $changed;
    }

    public function needsRefund(): bool
    {
        return $this->status === 'cancelled' && $this->payment_status === 'paid';
    }

    private function moveStatus(string $from, string $to): bool
    {
        $changed = self::whereKey($this->id)->where('status', $from)->update(['status' => $to]) === 1;
        $this->refresh();

        return $changed;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
