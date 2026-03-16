<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hasVaccination = $this->boolean('has_vaccination');

        return [
            'name'                              => ['required', 'string', 'max:255'],
            'phone'                             => ['required', 'string', 'max:20'],
            'address'                           => ['nullable', 'string', 'max:500'],
            'animal_type'                       => ['required', 'string', 'max:100'],
            'notes'                             => ['nullable', 'string', 'max:1000'],

            'consultation_price'                => ['required', 'numeric', 'min:0'],

            'has_vaccination'                   => ['nullable', 'boolean'],
            'vaccine_product_id'                => [
                $hasVaccination ? 'required' : 'nullable',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('is_active', true)->where('type', 'vaccination')),
            ],
            'vaccine_quantity'                  => [$hasVaccination ? 'required' : 'nullable', 'numeric', 'min:0.01'],
            'vaccine_unit_price'                => [$hasVaccination ? 'required' : 'nullable', 'numeric', 'min:0'],
            'vaccination_date'                  => [$hasVaccination ? 'required' : 'nullable', 'date'],
            'next_dose_date'                    => ['nullable', 'date', 'after:vaccination_date'],

            'additional_items'                  => ['nullable', 'array'],
            'additional_items.*.product_id'     => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'additional_items.*.quantity'        => ['required', 'numeric', 'min:0.01'],
            'additional_items.*.unit_price'      => ['required', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'               => __('customers.fields.name'),
            'phone'              => __('customers.fields.phone'),
            'animal_type'        => __('customers.fields.animal_type'),
            'consultation_price' => __('customers.visit.consultation_price'),
            'vaccine_product_id' => __('customers.visit.vaccine_product'),
            'vaccine_quantity'   => __('customers.visit.vaccine_quantity'),
            'vaccination_date'   => __('customers.visit.vaccination_date'),
            'next_dose_date'     => __('customers.visit.next_dose_date'),
        ];
    }
}
