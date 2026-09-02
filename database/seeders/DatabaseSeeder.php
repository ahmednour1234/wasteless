<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(WastelessNewSeeder::class);

        // Guarantees every Home screen section has data (see HomeScreenSeeder).
        $this->call(HomeScreenSeeder::class);

        // أدمن اختبار بصلاحيات كاملة على كل الموديولات.
        $this->call(SuperAdminSeeder::class);
    }
}
