<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Guest checkout form (docs §5.7). Anyone may check out, so there is nothing to authorize.
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Bangla digits and a +880 prefix are turned into the stored form (01XXXXXXXXX) before validating.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => normalize_phone($this->input('phone'))]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'delivery_zone_id' => ['required', 'integer', Rule::exists('delivery_zones', 'id')->where('is_active', true)],
            'district' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
