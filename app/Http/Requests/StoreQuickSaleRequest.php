<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuickSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'            => ['nullable', 'integer', Rule::exists('customers', 'id')],
            'customer_name'          => ['nullable', 'string', 'max:255'],
            'customer_phone'         => ['nullable', 'string', 'max:20'],

            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'items.*.quantity'       => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name'  => __('invoices.fields.customer_name'),
            'customer_phone' => __('invoices.fields.customer_phone'),
            'items'          => __('invoices.fields.items'),
        ];
    }
}
