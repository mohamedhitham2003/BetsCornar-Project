<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Vaccination;
use App\Models\VaccineBatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = FakerFactory::create('ar_SA'); // Use Arabic Faker

        DB::transaction(function () use ($faker) {
            $this->command->info('Creating Products, Services, and Vaccines...');

            $products = [];
            $vaccines = [];

            // 1. Generate 1000 products (mix of types)
            for ($i = 0; $i < 1000; $i++) {
                $type = $faker->randomElement(['product', 'service', 'vaccination']);
                $trackStock = $type !== 'service';
                $name = $type === 'vaccination' ? 'تطعيم ' . $faker->word() : ($type === 'service' ? 'خدمة ' . $faker->word() : 'منتج ' . $faker->word());

                $product = Product::updateOrCreate(
                    ['name' => $name], // Avoid duplicates by name
                    [
                        'type' => $type,
                        'price' => $faker->randomFloat(2, 50, 2000),
                        'quantity' => $trackStock ? $faker->randomFloat(2, 10, 500) : 0,
                        'track_stock' => $trackStock,
                        'stock_status' => $trackStock ? 'available' : 'available', // Assuming 'available' logic
                        'low_stock_threshold' => $trackStock ? 10 : 0,
                        'is_active' => true,
                        'notes' => $faker->optional(0.3)->sentence(),
                    ]
                );

                if ($type === 'vaccination') {
                    $vaccines[] = $product;
                } else {
                    $products[] = $product;
                }
            }

            $this->command->info('Products created: ' . count($products));
            $this->command->info('Vaccines created: ' . count($vaccines));

            if (empty($vaccines)) {
                $this->command->warn('No vaccines generated. Creating one manually to ensure batches can be made.');
                $vaccines[] = Product::updateOrCreate(
                    ['name' => 'تطعيم أساسي'],
                    [
                        'type' => 'vaccination',
                        'price' => 150,
                        'quantity' => 0,
                        'track_stock' => true,
                        'stock_status' => 'available',
                        'low_stock_threshold' => 10,
                        'is_active' => true,
                    ]
                );
            }

            // 2. 50 Vaccine batches
            $this->command->info('Creating Vaccine Batches...');
            for ($i = 0; $i < 50; $i++) {
                $vaccine = $faker->randomElement($vaccines);
                $qty = $faker->randomFloat(2, 10, 100);
                
                VaccineBatch::updateOrCreate(
                    ['batch_code' => 'BAT-' . $faker->unique()->numerify('#####')],
                    [
                        'product_id' => $vaccine->id,
                        'received_date' => Carbon::now()->subDays($faker->numberBetween(1, 60)),
                        'expiry_date' => Carbon::now()->addDays($faker->numberBetween(30, 365)), // Realistic expiry dates
                        'quantity_received' => $qty,
                        'quantity_remaining' => $qty, // Simple assumption: none used yet directly from batches
                    ]
                );
            }

            // Update vaccine quantities based on batches
            foreach ($vaccines as $vaccine) {
                $totalQty = VaccineBatch::where('product_id', $vaccine->id)->sum('quantity_remaining');
                $vaccine->update(['quantity' => $totalQty]);
            }

            // 3. 200 Customers with Egyptian numbers
            $this->command->info('Creating Customers...');
            $customers = [];
            for ($i = 0; $i < 200; $i++) {
                // Egyptian phone format 01[0,1,2,5] + 8 digits = 11 digits
                $prefix = $faker->randomElement(['010', '011', '012', '015']);
                $phone = $prefix . $faker->numerify('########');
                
                $customers[] = Customer::updateOrCreate(
                    ['phone' => $phone],
                    [
                        'name' => $faker->name(),
                        'address' => $faker->address(),
                        'animal_type' => $faker->randomElement(['قط', 'كلب', 'طائر', 'سلحفاة', 'أرنب', 'هامستر']),
                        'notes' => $faker->optional(0.2)->sentence(),
                    ]
                );
            }

            // Provide at least one consultation product
            $consultation = Product::updateOrCreate(
                ['name' => 'كشف عيادة'],
                [
                    'type' => 'service',
                    'price' => 100,
                    'quantity' => 0,
                    'track_stock' => false,
                    'stock_status' => 'available',
                    'is_active' => true,
                ]
            );


            // 4. 500 Invoices (mix of customer / quick_sale)
            $this->command->info('Creating Invoices and Items...');
            $invoices = [];
            for ($i = 0; $i < 500; $i++) {
                $source = $faker->randomElement(['customer', 'quick_sale']);
                $customer = null;
                $customerName = null;

                if ($source === 'customer') {
                    $customer = $faker->randomElement($customers);
                    $customerName = $customer->name;
                } else {
                    $customerName = $faker->name(); // Walk-in customer name
                }

                $invoice = Invoice::updateOrCreate(
                    ['invoice_number' => 'INV-' . $faker->unique()->numerify('######')],
                    [
                        'customer_id' => $customer ? $customer->id : null,
                        'customer_name' => $customerName,
                        'source' => $source,
                        'total' => 0, // Will calculate below
                        'status' => 'confirmed',
                        'created_at' => Carbon::now()->subDays($faker->numberBetween(0, 180)),
                    ]
                );
                
                $invoices[] = $invoice;

                // Create items for the invoice
                $numItems = $faker->numberBetween(1, 5);
                $invoiceTotal = 0;

                for ($j = 0; $j < $numItems; $j++) {
                    $itemProduct = current($products) ? $faker->randomElement($products) : $consultation; // Fallback to service
                    $qty = $faker->numberBetween(1, 3);
                    $lineTotal = $qty * $itemProduct->price;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $itemProduct->id,
                        'quantity' => $qty,
                        'unit_price' => $itemProduct->price,
                        'line_total' => $lineTotal,
                    ]);

                    $invoiceTotal += $lineTotal;
                }

                $invoice->update(['total' => $invoiceTotal]);
            }

            // 5. 300 Vaccinations
            $this->command->info('Creating Vaccinations...');
            $customerInvoices = array_filter($invoices, fn($inv) => $inv->source === 'customer');
            
            if (empty($customerInvoices)) {
                $this->command->warn('No customer invoices to attach vaccinations to. Skipping vaccinations.');
            } else {
                 for ($i = 0; $i < 300; $i++) {
                    $invoice = $faker->randomElement($customerInvoices);
                    $vaccine = $faker->randomElement($vaccines);
                    
                    // Spread dates randomly
                    $vaccinationDate = clone $invoice->created_at; 
                    
                    // Next dose: past (missed), present (due), future (upcoming)
                    $dateType = $faker->randomElement(['past', 'present', 'future']);
                    $nextDoseDate = match($dateType) {
                        'past' => Carbon::now()->subDays($faker->numberBetween(1, 60)),
                        'present' => Carbon::now()->addDays($faker->numberBetween(0, 7)), // Due within week
                        'future' => Carbon::now()->addDays($faker->numberBetween(8, 365)),
                    };

                    Vaccination::create([
                        'customer_id' => $invoice->customer_id,
                        'product_id' => $vaccine->id,
                        'invoice_id' => $invoice->id,
                        'vaccination_date' => $vaccinationDate,
                        'next_dose_date' => $faker->boolean(80) ? $nextDoseDate : null, // 80% have a next dose 
                    ]);
                }
            }
            
            $this->command->info('Test data generation completed successfully!');
        });
    }
}
