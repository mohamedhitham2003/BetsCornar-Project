<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\CustomerVisitService;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Consultation Product (كشف)
            Product::updateOrCreate(
                ['name' => 'كشف', 'type' => 'service'],
                [
                    'price' => 150.00,
                    'quantity' => 0,
                    'track_stock' => false,
                    'stock_status' => 'available',
                    'low_stock_threshold' => 0,
                    'is_active' => true,
                ]
            );

            // 2. Example products
            Product::updateOrCreate(
                ['name' => 'طوق براغيث', 'type' => 'product'],
                [
                    'price' => 75.00,
                    'quantity' => 20,
                    'track_stock' => true,
                    'stock_status' => 'available',
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                ]
            );

            Product::updateOrCreate(
                ['name' => 'قص أظافر', 'type' => 'service'],
                [
                    'price' => 50.00,
                    'quantity' => 0,
                    'track_stock' => false,
                    'stock_status' => 'available',
                    'low_stock_threshold' => 0,
                    'is_active' => true,
                ]
            );

            // 3. Example vaccines & batches
            $rabiesVaccine = Product::updateOrCreate(
                ['name' => 'تطعيم السعار', 'type' => 'vaccination'],
                [
                    'price' => 200.00,
                    'quantity' => 0,
                    'track_stock' => true,
                    'stock_status' => 'out_of_stock',
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                ]
            );

            $quadVaccine = Product::updateOrCreate(
                ['name' => 'التطعيم الرباعي', 'type' => 'vaccination'],
                [
                    'price' => 350.00,
                    'quantity' => 0,
                    'track_stock' => true,
                    'stock_status' => 'out_of_stock',
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                ]
            );

            $stockService = app(StockService::class);

            $stockService->createVaccineBatch([
                'product_id' => $rabiesVaccine->id,
                'batch_code' => 'RAB-2023-01',
                'received_date' => now()->subDays(10)->format('Y-m-d'),
                'expiry_date' => now()->addMonths(6)->format('Y-m-d'),
                'quantity_received' => 10,
                'quantity_remaining' => 10,
            ]);

            $stockService->createVaccineBatch([
                'product_id' => $quadVaccine->id,
                'batch_code' => 'QUAD-2023-01',
                'received_date' => now()->subDays(5)->format('Y-m-d'),
                'expiry_date' => now()->addMonths(12)->format('Y-m-d'),
                'quantity_received' => 15,
                'quantity_remaining' => 15,
            ]);

            $stockService->createVaccineBatch([
                'product_id' => $quadVaccine->id,
                'batch_code' => 'QUAD-EXPIRED',
                'received_date' => now()->subMonths(14)->format('Y-m-d'),
                'expiry_date' => now()->subDays(5)->format('Y-m-d'),
                'quantity_received' => 5,
                'quantity_remaining' => 5,
            ]);

            // 4. Example customers & visits
            $visitService = app(CustomerVisitService::class);

            // Visit 1: Dog, generic consultation + rabies vaccine
            $visitService->saveVisit([
                'name' => 'أحمد محمد',
                'phone' => '01012345678',
                'animal_type' => 'كلب',
                'consultation_price' => 150,
                'has_vaccination' => true,
                'vaccine_product_id' => $rabiesVaccine->id,
                'vaccine_quantity' => 1,
                'vaccination_date' => now()->format('Y-m-d'),
                'next_dose_date' => now()->addYear()->format('Y-m-d'),
                'additional_items' => [],
            ]);

            // Visit 2: Cat, consultation only
            $visitService->saveVisit([
                'name' => 'سارة عبده',
                'phone' => '01123456789',
                'animal_type' => 'قط',
                'address' => 'القاهرة',
                'consultation_price' => 150,
                'has_vaccination' => false,
                'additional_items' => [],
            ]);

            // Visit 3: Same cat from before (testing phone normalization and reuse), quad vaccine
            $visitService->saveVisit([
                'name' => 'سارة عبده',
                'phone' => '+20 11 2345 6789', // Different format, same normalized
                'animal_type' => 'قط',
                'consultation_price' => 0, // No consultation this time
                'has_vaccination' => true,
                'vaccine_product_id' => $quadVaccine->id,
                'vaccine_quantity' => 1,
                'vaccination_date' => now()->format('Y-m-d'),
                'next_dose_date' => now()->addDays(21)->format('Y-m-d'),
                'additional_items' => [],
            ]);

        });
    }
}
