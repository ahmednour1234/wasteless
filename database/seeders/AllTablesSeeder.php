<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AllTablesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();
        $now = now();

        DB::table('roles')->insertOrIgnore([
            ['name' => 'Admin', 'data' => json_encode(['all' => true]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manager', 'data' => json_encode(['dashboard' => true]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'User', 'data' => json_encode(['orders' => true]), 'created_at' => $now, 'updated_at' => $now],
        ]);

        $roleIds = DB::table('roles')->pluck('id')->all();

        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->numerify('03######'),
                'password' => Hash::make('password'),
                'role_id' => $faker->randomElement($roleIds),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $users[] = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '03000000',
            'password' => Hash::make('password'),
            'role_id' => $roleIds[0],
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('users')->insert($users);

        $customers = [];
        for ($i = 1; $i <= 15; $i++) {
            $customers[] = [
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->numerify('71######'),
                'img' => null,
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('customers')->insert($customers);

        $companies = [];
        for ($i = 1; $i <= 6; $i++) {
            $companies[] = [
                'name' => $faker->company(),
                'phone' => '81' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'email' => 'company' . $i . '@example.com',
                'password' => Hash::make('password'),
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('companies')->insert($companies);

        $companyIds = DB::table('companies')->pluck('id')->all();

        $branches = [];
        foreach ($companyIds as $companyId) {
            for ($i = 1; $i <= 2; $i++) {
                $branches[] = [
                    'lat' => $faker->randomFloat(7, 33, 34),
                    'lng' => $faker->randomFloat(7, 35, 36),
                    'name' => 'Branch ' . $companyId . '-' . $i,
                    'address' => $faker->address(),
                    'phone' => $faker->numerify('76######'),
                    'company_id' => $companyId,
                    'main' => $i === 1,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('branches')->insert($branches);

        $categories = [];
        for ($i = 1; $i <= 8; $i++) {
            $categories[] = [
                'name' => ucfirst($faker->unique()->word()),
                'image' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('categories')->insert($categories);

        $branchRows = DB::table('branches')->get(['id', 'company_id']);
        $categoryIds = DB::table('categories')->pluck('id')->all();

        $bundles = [];
        foreach ($branchRows as $branch) {
            for ($i = 1; $i <= 2; $i++) {
                $price = $faker->randomFloat(2, 10, 250);
                $discounted = $faker->boolean(40) ? round($price * 0.85, 2) : null;
                $bundles[] = [
                    'company_id' => $branch->company_id,
                    'branch_id' => $branch->id,
                    'category_id' => $faker->randomElement($categoryIds),
                    'name' => $faker->words(3, true),
                    'image' => null,
                    'description' => $faker->sentence(),
                    'price' => $price,
                    'price_after_discount' => $discounted,
                    'stock' => $faker->numberBetween(1, 100),
                    'active' => true,
                    'opening_time' => $now,
                    'ended_time' => $now->copy()->addHours(6)->format('H:i:s'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('bundles')->insert($bundles);

        $customerIds = DB::table('customers')->pluck('id')->all();

        $orders = [];
        for ($i = 1; $i <= 20; $i++) {
            $subTotal = $faker->randomFloat(2, 20, 500);
            $discount = $faker->randomFloat(2, 0, 30);
            $commissionPercent = 10;
            $orders[] = [
                'customer_id' => $faker->randomElement($customerIds),
                'status' => $faker->randomElement(['pending', 'confirmed', 'delivered', 'cancelled']),
                'sub_total' => $subTotal,
                'total_discount' => $discount,
                'delivery' => $faker->randomFloat(2, 0, 10),
                'commission_percentage' => $commissionPercent,
                'commission_amount' => round($subTotal * ($commissionPercent / 100), 2),
                'address' => $faker->address(),
                'phone' => $faker->numerify('70######'),
                'name' => $faker->name(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('orders')->insert($orders);

        $bundleRows = DB::table('bundles')->get(['id', 'company_id', 'branch_id', 'category_id', 'price', 'price_after_discount']);
        $orderIds = DB::table('orders')->pluck('id')->all();

        $orderDetails = [];
        foreach ($orderIds as $orderId) {
            $itemsCount = $faker->numberBetween(1, 3);
            for ($i = 0; $i < $itemsCount; $i++) {
                $bundle = $faker->randomElement($bundleRows->all());
                $quantity = $faker->numberBetween(1, 4);
                $unitPrice = $bundle->price_after_discount ?? $bundle->price;
                $discount = $bundle->price_after_discount ? ($bundle->price - $bundle->price_after_discount) : 0;
                $orderDetails[] = [
                    'order_id' => $orderId,
                    'bundle_id' => $bundle->id,
                    'company_id' => $bundle->company_id,
                    'branch_id' => $bundle->branch_id,
                    'category_id' => $bundle->category_id,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'discount' => $discount,
                    'total' => round($unitPrice * $quantity, 2),
                    'bundles' => json_encode(['name' => 'bundle-' . $bundle->id]),
                    'status' => $faker->randomElement(['pending', 'preparing', 'done', 'cancelled']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('order_details')->insert($orderDetails);

        $transactions = [];
        foreach ($orderIds as $orderId) {
            $amount = $faker->randomFloat(2, 20, 500);
            $commissionPercent = 10;
            $transactions[] = [
                'order_id' => $orderId,
                'external_id' => 'TX-' . strtoupper(Str::random(12)) . '-' . $orderId,
                'payment_type' => $faker->randomElement(['whish_money', 'omt_pay', 'bank']),
                'amount' => $amount,
                'currency' => 'USD',
                'commission_percentage' => $commissionPercent,
                'commission_amount' => round($amount * ($commissionPercent / 100), 2),
                'status' => $faker->randomElement(['pending', 'success', 'failed', 'cancelled']),
                'collect_url' => $faker->url(),
                'collect_status' => $faker->randomElement(['new', 'processing', 'completed']),
                'payer_phone_number' => $faker->numerify('79######'),
                'invoice' => 'INV-' . strtoupper(Str::random(8)),
                'success_callback_url' => 'https://example.com/success',
                'failure_callback_url' => 'https://example.com/failure',
                'success_redirect_url' => 'https://example.com/success-redirect',
                'failure_redirect_url' => 'https://example.com/failure-redirect',
                'metadata' => json_encode(['source' => 'seeder']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('transactions')->insert($transactions);

        $reviews = [];
        for ($i = 1; $i <= 25; $i++) {
            $bundle = $faker->randomElement($bundleRows->all());
            $reviews[] = [
                'bundle_id' => $bundle->id,
                'customer_id' => $faker->randomElement($customerIds),
                'bundle_data' => json_encode([
                    'bundle_name' => $bundle->id,
                    'seeded' => true,
                ]),
                'rating' => $faker->numberBetween(1, 5),
                'comment' => $faker->sentence(),
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('reviews')->insert($reviews);

        $projects = [];
        for ($i = 1; $i <= 4; $i++) {
            $projects[] = [
                'name' => 'Project ' . $i,
                'name_ar' => 'Project AR ' . $i,
                'title' => 'Landing ' . $i,
                'title_ar' => 'Landing AR ' . $i,
                'qrcode' => '/qrcode/project-' . $i . '.png',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('projects')->insert($projects);

        $projectIds = DB::table('projects')->pluck('id')->all();
        $pdfs = [];
        foreach ($projectIds as $projectId) {
            for ($i = 1; $i <= 2; $i++) {
                $pdfs[] = [
                    'project_id' => $projectId,
                    'name' => 'PDF ' . $projectId . '-' . $i,
                    'name_ar' => 'PDF AR ' . $projectId . '-' . $i,
                    'qrcode' => '/qrcode/pdf-' . $projectId . '-' . $i . '.png',
                    'pdf' => '/pdfs/file-' . $projectId . '-' . $i . '.pdf',
                    'size' => $faker->numberBetween(100, 5000),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('pdfs')->insert($pdfs);

        DB::table('password_reset_tokens')->insertOrIgnore([
            'email' => 'test@example.com',
            'token' => Str::random(60),
            'created_at' => $now,
        ]);

        $firstUserId = DB::table('users')->value('id');
        if ($firstUserId) {
            DB::table('personal_access_tokens')->insert([
                'tokenable_type' => 'App\\Models\\User',
                'tokenable_id' => $firstUserId,
                'name' => 'seed-token',
                'token' => hash('sha256', Str::random(40)),
                'abilities' => json_encode(['*']),
                'last_used_at' => null,
                'expires_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'SeedJob']),
            'exception' => 'Seeded failed job row for testing.',
            'failed_at' => $now,
        ]);

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'commission_percentage')) {
            DB::statement('UPDATE settings SET commission_percentage = 10 WHERE commission_percentage IS NULL');
        }
    }
}
