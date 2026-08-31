<?php

namespace Database\Seeders;

use App\Models\Bundle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * QA seed لحالات شارة التوفر (availability badge) وكارت "sold out".
 *
 * التشغيل:  php artisan db:seed --class=AvailabilityTestBundleSeeder
 * الحذف:    Bundle::where('name', 'LIKE', '[qa]%')->delete();
 */
class AvailabilityTestBundleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now(config('app.timezone'));

        $shared = [
            'company_id'  => 1,
            'branch_id'   => 1,
            'category_id' => 1,
            'image'       => 'https://images.unsplash.com/photo-1519378058457-4c29a0a2efac?w=800',
            'description' => 'QA availability seed — safe to delete',
            'price'       => 9.00,
            'price_after_discount' => 3.99,
            'active'      => true,
        ];

        // فترة الاستلام لازم تكون مفتوحة دلوقتي، وإلا فلتر index
        // (opening_time <= now) هيستبعد الحالات دي من الهوم.
        // والإغلاق بعد 6 ساعات يخلي minutes_left > 60 فيظهر شارة المخزون.
        $open  = $now->copy()->subHour();
        $close = $now->copy()->addHours(6);

        $rows = [
            ['name' => '[qa] 5+ left', 'stock' => 8, 'opening_time' => $open,  'ended_time' => $close],
            ['name' => '[qa] 5 left',  'stock' => 5, 'opening_time' => $open,  'ended_time' => $close],
            ['name' => '[qa] 4 left',  'stock' => 4, 'opening_time' => $open,  'ended_time' => $close],
            ['name' => '[qa] 3 left',  'stock' => 3, 'opening_time' => $open,  'ended_time' => $close],
            ['name' => '[qa] 2 left',  'stock' => 2, 'opening_time' => $open,  'ended_time' => $close],
            ['name' => '[qa] 1 left',  'stock' => 1, 'opening_time' => $open,  'ended_time' => $close],
            [
                'name'         => '[qa] 14 min left',
                'stock'        => 3,
                'opening_time' => $now->copy()->subHour(),
                'ended_time'   => $now->copy()->addMinutes(14),
            ],
            [
                'name'         => '[qa] Sold out',
                'stock'        => 0,
                'opening_time' => $now->copy()->subHours(3),
                'ended_time'   => $now->copy()->addHour(),
            ],
            [
                'name'         => '[qa] Ended Arabic',
                'stock'        => 5,
                'opening_time' => $now->copy()->subHours(8),
                'ended_time'   => $now->copy()->subHour(),
            ],
        ];

        foreach ($rows as $row) {
            Bundle::updateOrCreate(
                ['name' => $row['name']],
                array_merge($shared, $row),
            );
        }
    }
}
