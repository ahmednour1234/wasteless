<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Services\PromoCodeService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Valid promo codes + paid orders that used them, so the dashboard
 * (Orders → Promo Code column, Promo Codes → Usages) has real data.
 *
 * Non-destructive: codes are upserted by `code`; the orders it owns are tagged
 * by address and replaced on each run, so it is safe to re-run on a live environment.
 *
 * Usage:  php artisan db:seed --class=PromoCodeSeeder --force
 */
class PromoCodeSeeder extends Seeder
{
    /** Marks the orders this seeder owns, so re-runs replace instead of duplicate. */
    private const TAG = '[promo-seed]';

    private const ORDERS_TO_CREATE = 20;

    public function run(): void
    {
        DB::transaction(function () {
            $codes = $this->seedCodes();
            $this->clearSeededOrders();
            $this->seedOrders($codes);
        });
    }

    /** @return \Illuminate\Support\Collection<PromoCode> */
    private function seedCodes()
    {
        $now = Carbon::now();

        $codes = [
            ['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10, 'max_discount' => null, 'min_order_amount' => null, 'usage_limit' => null, 'usage_limit_per_user' => 1,    'end' => $now->copy()->addYear()],
            ['code' => 'SAVE20',    'type' => 'percentage', 'value' => 20, 'max_discount' => 10,   'min_order_amount' => null, 'usage_limit' => 1000, 'usage_limit_per_user' => 3,    'end' => $now->copy()->addMonths(3)],
            ['code' => 'SAVE30',    'type' => 'percentage', 'value' => 30, 'max_discount' => 15,   'min_order_amount' => 20,   'usage_limit' => 300,  'usage_limit_per_user' => 2,    'end' => $now->copy()->addMonths(2)],
            ['code' => 'HALFOFF',   'type' => 'percentage', 'value' => 50, 'max_discount' => 8,    'min_order_amount' => null, 'usage_limit' => 100,  'usage_limit_per_user' => 1,    'end' => $now->copy()->addMonth()],
            ['code' => 'FLAT3',     'type' => 'fixed',      'value' => 3,  'max_discount' => null, 'min_order_amount' => null, 'usage_limit' => null, 'usage_limit_per_user' => 5,    'end' => $now->copy()->addMonths(6)],
            ['code' => 'FLAT5',     'type' => 'fixed',      'value' => 5,  'max_discount' => null, 'min_order_amount' => 15,   'usage_limit' => 500,  'usage_limit_per_user' => 2,    'end' => $now->copy()->addMonths(6)],
            ['code' => 'NOLIMIT',   'type' => 'fixed',      'value' => 2,  'max_discount' => null, 'min_order_amount' => null, 'usage_limit' => null, 'usage_limit_per_user' => null, 'end' => null],
        ];

        return collect($codes)->map(function ($c) use ($now) {
            $end = $c['end'];
            unset($c['end']);

            return PromoCode::updateOrCreate(['code' => $c['code']], $c + [
                'start_date' => $now->copy()->subMonth(),
                'end_date'   => $end,
                'is_active'  => true,
            ]);
        });
    }

    private function clearSeededOrders(): void
    {
        $orderIds = Order::where('address', self::TAG)->pluck('id');

        PromoCodeUsage::whereIn('order_id', $orderIds)->delete();
        OrderDetail::whereIn('order_id', $orderIds)->delete();
        Order::whereIn('id', $orderIds)->delete();
    }

    private function seedOrders($codes): void
    {
        $customers = Customer::inRandomOrder()->take(10)->get();
        $bundles   = Bundle::where('active', 1)->inRandomOrder()->take(15)->get();

        if ($customers->isEmpty() || $bundles->isEmpty()) {
            $this->command?->warn('PromoCodeSeeder: no customers or active bundles — only codes were seeded.');
            return;
        }

        $service = new PromoCodeService();
        $created = 0;

        for ($i = 0; $i < self::ORDERS_TO_CREATE * 3 && $created < self::ORDERS_TO_CREATE; $i++) {
            $customer = $customers->random();
            $promo    = $codes->random();
            $bundle   = $bundles->random();
            $quantity = rand(1, 3);

            $price        = (float) $bundle->price;
            $finalPrice   = (float) ($bundle->price_after_discount ?? $price);
            $subTotal     = $price * $quantity;
            $bundleDisc   = ($price - $finalPrice) * $quantity;
            $amount       = $subTotal - $bundleDisc;

            // Same rules as the real checkout; skip combos that would be rejected (limits, min amount).
            try {
                $discount = $service->validate($promo->code, $customer, $amount)['discount'];
            } catch (\Exception $e) {
                continue;
            }

            $createdAt = Carbon::now()->subDays(rand(0, 25))->subMinutes(rand(0, 1440));

            $order = Order::create([
                'customer_id'    => $customer->id,
                'status'         => collect(['pending', 'completed', 'completed'])->random(),
                'sub_total'      => $subTotal,
                'total_discount' => $bundleDisc,
                'delivery'       => 0,
                'address'        => self::TAG,
                'name'           => $customer->name,
                'phone'          => $customer->phone,
            ] + $service->toMetadata($promo, $discount));

            $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            OrderDetail::create([
                'order_id'    => $order->id,
                'bundle_id'   => $bundle->id,
                'company_id'  => $bundle->company_id,
                'branch_id'   => $bundle->branch_id,
                'category_id' => $bundle->category_id,
                'quantity'    => $quantity,
                'price'       => $price,
                'discount'    => $price - $finalPrice,
                'total'       => $subTotal,
                'bundles'     => $bundle->toArray(),
                'status'      => $order->status,
            ]);

            PromoCodeService::recordUsage($order);
            PromoCodeUsage::where('order_id', $order->id)->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);

            $created++;
        }

        $this->command?->info("PromoCodeSeeder: {$codes->count()} codes, {$created} orders with promo codes.");
    }
}
