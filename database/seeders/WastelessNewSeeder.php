<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WastelessNewSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create();
        $now = Carbon::now();

        $this->truncateSeedTables();

        $this->seedRoles($now);
        $this->seedUsers($faker, $now);
        $this->seedCustomers($faker, $now);
        $this->seedCompanies($faker, $now);
        $this->seedBranches($faker, $now);
        $this->seedCategories($now);
        $this->seedBundles($faker, $now);
        $this->seedOrders($faker, $now);
        $this->seedOrderDetails($faker, $now);
        $this->seedTransactions($faker, $now);
        $this->seedReviews($faker, $now);
        $this->seedProjectsAndPdfs($now);
        $this->seedPasswordResetTokens($now);
        $this->seedPersonalAccessTokens($now);
        $this->seedFailedJobs($now);
        $this->seedSettings($now);
    }

    private function truncateSeedTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'order_details',
            'reviews',
            'transactions',
            'orders',
            'bundles',
            'branches',
            'pdfs',
            'projects',
            'personal_access_tokens',
            'password_reset_tokens',
            'users',
            'customers',
            'companies',
            'categories',
            'roles',
            'failed_jobs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function seedRoles(Carbon $now): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        DB::table('roles')->insert([
            ['name' => 'Default Role', 'data' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Admin', 'data' => json_encode(['all' => true]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manager', 'data' => json_encode(['dashboard' => true]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'User', 'data' => json_encode(['orders' => true]), 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedUsers($faker, Carbon $now): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('roles')) {
            return;
        }

        $roleIds = DB::table('roles')->pluck('id')->all();
        if (empty($roleIds)) {
            return;
        }

        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'name' => $faker->name(),
                'email' => 'user' . $i . '@example.com',
                'phone' => '03' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
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
    }

    private function seedCustomers($faker, Carbon $now): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        $customers = [
            [
                'name' => 'ahmedbour',
                'email' => 'ahmednour5999@gmail.com',
                'phone' => '+9615689899',
                'img' => null,
                'password' => Hash::make('password'),
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        for ($i = 2; $i <= 16; $i++) {
            $customers[] = [
                'name' => $faker->name(),
                'email' => 'customer' . $i . '@example.com',
                'phone' => '71' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'img' => null,
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('customers')->insert($customers);
    }

    private function seedCompanies($faker, Carbon $now): void
    {
        if (!Schema::hasTable('companies')) {
            return;
        }

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
    }

    private function seedBranches($faker, Carbon $now): void
    {
        if (!Schema::hasTable('branches') || !Schema::hasTable('companies')) {
            return;
        }

        $companyIds = DB::table('companies')->pluck('id')->all();
        if (empty($companyIds)) {
            return;
        }

        $branches = [];
        foreach ($companyIds as $companyId) {
            for ($i = 1; $i <= 2; $i++) {
                $branches[] = [
                    'lat' => $faker->randomFloat(7, 33, 34),
                    'lng' => $faker->randomFloat(7, 35, 36),
                    'name' => 'Branch ' . $companyId . '-' . $i,
                    'address' => $faker->address(),
                    'phone' => '76' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                    'company_id' => $companyId,
                    'main' => $i === 1,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('branches')->insert($branches);
    }

    private function seedCategories(Carbon $now): void
    {
        if (!Schema::hasTable('categories')) {
            return;
        }

        $categories = [
            ['name' => 'EWQEQWQWEQEW', 'image' => null, 'is_active' => true, 'created_at' => null, 'updated_at' => null],
            ['name' => 'EWQEQWQWEQEW', 'image' => null, 'is_active' => true, 'created_at' => null, 'updated_at' => null],
            ['name' => 'Maxime', 'image' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Quia', 'image' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Id', 'image' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rerum', 'image' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Minus', 'image' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sed', 'image' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Natus', 'image' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Molestiae', 'image' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('categories')->insert($categories);
    }

    private function seedBundles($faker, Carbon $now): void
    {
        if (!Schema::hasTable('bundles') || !Schema::hasTable('branches') || !Schema::hasTable('categories')) {
            return;
        }

        $branches = DB::table('branches')->get(['id', 'company_id']);
        $categoryIds = DB::table('categories')->pluck('id')->all();

        if ($branches->isEmpty() || empty($categoryIds)) {
            return;
        }

        $bundles = [];
        foreach ($branches as $branch) {
            for ($i = 1; $i <= 2; $i++) {
                $price = $faker->randomFloat(2, 10, 250);
                $hasDiscount = (bool) random_int(0, 1);
                $priceAfterDiscount = $hasDiscount ? round($price * 0.85, 2) : null;

                $bundles[] = [
                    'company_id' => $branch->company_id,
                    'branch_id' => $branch->id,
                    'category_id' => $faker->randomElement($categoryIds),
                    'name' => $faker->words(3, true),
                    'image' => null,
                    'description' => $faker->sentence(),
                    'price' => $price,
                    'price_after_discount' => $priceAfterDiscount,
                    'stock' => random_int(1, 100),
                    'active' => true,
                    'opening_time' => $now->copy()->addHours(3),
                    'ended_time' => $now->copy()->addHours(9)->format('H:i:s'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('bundles')->insert($bundles);
    }

    private function seedOrders($faker, Carbon $now): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasTable('customers')) {
            return;
        }

        $customerIds = DB::table('customers')->pluck('id')->all();
        if (empty($customerIds)) {
            return;
        }

        $orders = [];
        for ($i = 1; $i <= 20; $i++) {
            $orders[] = [
                'customer_id' => $faker->randomElement($customerIds),
                'status' => $faker->randomElement(['pending', 'confirmed', 'delivered', 'cancelled']),
                'sub_total' => $faker->randomFloat(2, 20, 500),
                'total_discount' => $faker->randomFloat(2, 0, 30),
                'delivery' => $faker->randomFloat(2, 0, 15),
                'address' => $faker->address(),
                'phone' => '70' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'name' => $faker->name(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('orders')->insert($orders);
    }

    private function seedOrderDetails($faker, Carbon $now): void
    {
        if (!Schema::hasTable('order_details') || !Schema::hasTable('orders') || !Schema::hasTable('bundles')) {
            return;
        }

        $orders = DB::table('orders')->pluck('id')->all();
        $bundles = DB::table('bundles')->get(['id', 'company_id', 'branch_id', 'category_id', 'price', 'price_after_discount']);

        if (empty($orders) || $bundles->isEmpty()) {
            return;
        }

        $rows = [];
        foreach ($orders as $orderId) {
            $count = random_int(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $bundle = $faker->randomElement($bundles->all());
                $quantity = random_int(1, 4);
                $unitPrice = $bundle->price_after_discount ?? $bundle->price;
                $discount = $bundle->price_after_discount ? ($bundle->price - $bundle->price_after_discount) : 0;

                $rows[] = [
                    'order_id' => $orderId,
                    'bundle_id' => $bundle->id,
                    'company_id' => $bundle->company_id,
                    'branch_id' => $bundle->branch_id,
                    'category_id' => $bundle->category_id,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'discount' => $discount,
                    'total' => round($unitPrice * $quantity, 2),
                    'bundles' => json_encode(['bundle_id' => $bundle->id, 'seeded' => true]),
                    'status' => $faker->randomElement(['pending', 'preparing', 'done', 'cancelled']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('order_details')->insert($rows);
    }

    private function seedTransactions($faker, Carbon $now): void
    {
        if (!Schema::hasTable('transactions') || !Schema::hasTable('orders')) {
            return;
        }

        $orderIds = DB::table('orders')->pluck('id')->all();
        if (empty($orderIds)) {
            return;
        }

        $rows = [];
        foreach ($orderIds as $orderId) {
            $rows[] = [
                'order_id' => $orderId,
                'external_id' => 'TX-' . strtoupper(Str::random(10)) . '-' . $orderId,
                'payment_type' => $faker->randomElement(['whish_money', 'omt_pay', 'bank']),
                'amount' => $faker->randomFloat(2, 20, 500),
                'currency' => 'USD',
                'status' => $faker->randomElement(['pending', 'success', 'failed', 'cancelled']),
                'collect_url' => $faker->url(),
                'collect_status' => $faker->randomElement(['new', 'processing', 'completed']),
                'payer_phone_number' => '79' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'invoice' => 'INV-' . strtoupper(Str::random(8)),
                'success_callback_url' => 'https://example.com/success',
                'failure_callback_url' => 'https://example.com/failure',
                'success_redirect_url' => 'https://example.com/success-redirect',
                'failure_redirect_url' => 'https://example.com/failure-redirect',
                'metadata' => json_encode(['source' => 'wasteless_new_seeder']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('transactions')->insert($rows);
    }

    private function seedReviews($faker, Carbon $now): void
    {
        if (!Schema::hasTable('reviews') || !Schema::hasTable('bundles') || !Schema::hasTable('customers')) {
            return;
        }

        $bundleIds = DB::table('bundles')->pluck('id')->all();
        $customerIds = DB::table('customers')->pluck('id')->all();
        if (empty($bundleIds) || empty($customerIds)) {
            return;
        }

        $rows = [];
        for ($i = 1; $i <= 30; $i++) {
            $bundleId = $faker->randomElement($bundleIds);
            $rows[] = [
                'bundle_id' => $bundleId,
                'customer_id' => $faker->randomElement($customerIds),
                'bundle_data' => json_encode(['bundle_id' => $bundleId]),
                'rating' => random_int(1, 5),
                'comment' => $faker->sentence(),
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('reviews')->insert($rows);
    }

    private function seedProjectsAndPdfs(Carbon $now): void
    {
        if (Schema::hasTable('projects')) {
            DB::table('projects')->insert([
                ['name' => 'Project 1', 'name_ar' => 'Project AR 1', 'title' => 'Landing 1', 'title_ar' => 'Landing AR 1', 'qrcode' => '/qrcode/project-1.png', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Project 2', 'name_ar' => 'Project AR 2', 'title' => 'Landing 2', 'title_ar' => 'Landing AR 2', 'qrcode' => '/qrcode/project-2.png', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Project 3', 'name_ar' => 'Project AR 3', 'title' => 'Landing 3', 'title_ar' => 'Landing AR 3', 'qrcode' => '/qrcode/project-3.png', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Project 4', 'name_ar' => 'Project AR 4', 'title' => 'Landing 4', 'title_ar' => 'Landing AR 4', 'qrcode' => '/qrcode/project-4.png', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (!Schema::hasTable('pdfs') || !Schema::hasTable('projects')) {
            return;
        }

        $projectIds = DB::table('projects')->pluck('id')->all();
        if (empty($projectIds)) {
            return;
        }

        $rows = [];
        foreach ($projectIds as $projectId) {
            for ($i = 1; $i <= 2; $i++) {
                $rows[] = [
                    'project_id' => $projectId,
                    'name' => 'PDF ' . $projectId . '-' . $i,
                    'name_ar' => 'PDF AR ' . $projectId . '-' . $i,
                    'qrcode' => '/qrcode/pdf-' . $projectId . '-' . $i . '.png',
                    'pdf' => '/pdfs/file-' . $projectId . '-' . $i . '.pdf',
                    'size' => random_int(120, 5000),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('pdfs')->insert($rows);
    }

    private function seedPasswordResetTokens(Carbon $now): void
    {
        if (!Schema::hasTable('password_reset_tokens')) {
            return;
        }

        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Str::random(60),
            'created_at' => $now,
        ]);
    }

    private function seedPersonalAccessTokens(Carbon $now): void
    {
        if (!Schema::hasTable('personal_access_tokens') || !Schema::hasTable('customers')) {
            return;
        }

        $customerId = DB::table('customers')->value('id');
        if (!$customerId) {
            return;
        }

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\\Models\\Customer',
            'tokenable_id' => $customerId,
            'name' => 'user-token',
            'token' => hash('sha256', Str::random(40)),
            'abilities' => json_encode(['*']),
            'last_used_at' => $now,
            'expires_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedFailedJobs(Carbon $now): void
    {
        if (!Schema::hasTable('failed_jobs')) {
            return;
        }

        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'SeedJob']),
            'exception' => 'Seeded failed job row for local testing.',
            'failed_at' => $now,
        ]);
    }

    private function seedSettings(Carbon $now): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'شركة الإمتياز للإستقدام',
                'img' => '1759760768-33.png',
                'phone' => '+920016673',
                'address' => 'Saudi Arabia',
                'lang' => '46.771740298567266',
                'lat' => '24.701926957911873',
                'email' => 'Alemteyaz@gmail.com',
                'social_media' => json_encode([
                    ['type' => 'facebook', 'link' => 'https://www.facebook.com/profile.php?id=61579599496928&locale=ar_AR'],
                    ['type' => 'instagram', 'link' => 'https://www.instagram.com/alemtayaz_rc/'],
                    ['type' => 'snapchat', 'link' => 'https://www.snapchat.com/alemteyaz0'],
                    ['type' => 'tiktok', 'link' => 'https://www.tiktok.com/@alemtayaz_rc'],
                    ['type' => 'x', 'link' => 'https://x.com/alemtayaz_rc'],
                ]),
                'view_web' => 7102,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
