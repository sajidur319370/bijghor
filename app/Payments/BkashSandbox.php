<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * bKash Tokenized Checkout in SANDBOX (test) mode (docs §5.8). Test money only.
 *
 * create/execute are never retried automatically: they move money and have no idempotency key.
 * Timeouts and connection failures throw; SettlePayment then asks /payment/status instead.
 */
class BkashSandbox implements PaymentGateway
{
    private const TOKEN_CACHE_KEY = 'bkash_id_token';

    public function create(Order $order): array
    {
        return $this->post('/create', [
            'mode' => '0011',
            'payerReference' => $order->customer_phone,
            'callbackURL' => route('bkash.callback'),
            'amount' => number_format((float) $order->total, 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $order->order_number,
        ]);
    }

    public function execute(string $paymentId): array
    {
        return $this->post('/execute', ['paymentID' => $paymentId]);
    }

    public function status(string $paymentId): array
    {
        return $this->post('/payment/status', ['paymentID' => $paymentId]);
    }

    /**
     * @param  array<string, string>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload): array
    {
        $response = $this->client()->post($path, $payload);

        if ($response->unauthorized()) {
            Cache::forget(self::TOKEN_CACHE_KEY);   // token expired early: fetch a new one next time
        }

        $body = $response->json();
        $body = is_array($body) ? $body : [];

        Log::channel('daily')->info('bkash '.$path, ['http_status' => $response->status(), 'response' => $body]);

        return $body;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl((string) config('services.bkash.base_url'))
            ->withHeaders([
                'Authorization' => $this->token(),
                'X-APP-Key' => (string) config('services.bkash.app_key'),
            ])
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(30);
    }

    /** id_token is valid for about an hour; cache it for 50 minutes. */
    private function token(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(50), function (): string {
            $response = Http::baseUrl((string) config('services.bkash.base_url'))
                ->withHeaders([
                    'username' => (string) config('services.bkash.username'),
                    'password' => (string) config('services.bkash.password'),
                ])
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(15)
                ->post('/token/grant', [
                    'app_key' => (string) config('services.bkash.app_key'),
                    'app_secret' => (string) config('services.bkash.app_secret'),
                ])
                ->throw();

            $token = $response->json('id_token');

            if (! is_string($token) || $token === '') {
                throw new RuntimeException('bKash token grant failed: '.($response->json('statusMessage') ?? 'no id_token'));
            }

            return $token;
        });
    }
}
