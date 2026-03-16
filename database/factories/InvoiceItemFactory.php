<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        $qty = fake()->randomFloat(2, 1, 5);
        $price = fake()->randomFloat(2, 50, 200);

        return [
            'invoice_id' => \App\Models\Invoice::factory(),
            'product_id' => \App\Models\Product::factory(),
            'quantity' => $qty,
            'unit_price' => $price,
            'line_total' => $qty * $price,
        ];
    }
}
