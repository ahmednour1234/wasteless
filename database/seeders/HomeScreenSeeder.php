<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the data the Home screen needs so that none of its sections come back empty.
 *
 * Each section hides itself when its endpoint returns an empty `data` array, so this
 * seeder creates at least one bundle matching every filter used by
 * App\Http\Controllers\API\User\BundleController, plus the categories grid.
 *
 * Non-destructive: it never truncates. Rows it owns are tagged by name prefix and
 * refreshed on each run, so it is safe to re-run on a live environment.
 *
 * Usage:  php artisan db:seed --class=HomeScreenSeeder
 */
class HomeScreenSeeder extends Seeder
{
    /** Marks the rows this seeder owns, so re-runs replace instead of duplicate. */
    private const TAG = '[home]';

    private ?bool $endedIsDateTime = null;

    public function run(): void
    {
        if (! Schema::hasTable('bundles') || ! Schema::hasTable('categories')) {
            $this->command?->error('bundles/categories tables missing - run migrations first.');
            return;
        }

        $faker = FakerFactory::create();
        // The controllers mix now() and Carbon::now('Africa/Cairo'); config/app.php
        // already sets Africa/Cairo, so both resolve to this same instant.
        $now = Carbon::now('Africa/Cairo');

        $this->seedCategories($now);

        $branch = $this->resolveBranch();
        if (! $branch) {
            $this->command?->error('No branch/company found - seed companies and branches first.');
            return;
        }

        $this->clearOwnedBundles();

        $categoryIds = DB::table('categories')->pluck('id')->all();

        $rows = array_merge(
            $this->popularRows($faker, $now, $branch, $categoryIds),
            $this->lastChanceRows($faker, $now, $branch, $categoryIds),
            $this->lastHourRows($faker, $now, $branch, $categoryIds),
            $this->tomorrowRows($faker, $now, $branch, $categoryIds),
        );

        DB::table('bundles')->insert($rows);

        $this->report($now);
    }

    /* ---------------------------------------------------------------
    | Categories  ->  GET user/categories
    |
    | The Home grid shimmers forever when this is empty, so it is the
    | one list that must never be missing.
    ---------------------------------------------------------------- */
    private function seedCategories(Carbon $now): void
    {
        $names = ['Bakery', 'Groceries', 'Restaurants', 'Cafe', 'Desserts', 'Fruits & Vegetables'];

        foreach ($names as $name) {
            // Keyed by name so re-running tops up without duplicating.
            DB::table('categories')->updateOrInsert(
                ['name' => $name],
                ['is_active' => true, 'updated_at' => $now, 'created_at' => $now],
            );
        }
    }

    /* ---------------------------------------------------------------
    | Popular around you  ->  GET user/bundles
    | Needs: active, stock > 0, opening_time <= now, ended_time >= now
    ---------------------------------------------------------------- */
    private function popularRows($faker, Carbon $now, object $branch, array $categoryIds): array
    {
        $rows = [];
        for ($i = 1; $i <= 6; $i++) {
            $rows[] = $this->bundle($faker, $now, $branch, $categoryIds, [
                'name'         => self::TAG . ' Popular ' . $i,
                'stock'        => random_int(5, 40),
                // Already open, and still running for hours.
                'opening_time' => $now->copy()->subHours(2),
                'ended_time'   => $now->copy()->addHours(6),
            ]);
        }

        return $rows;
    }

    /* ---------------------------------------------------------------
    | Last chance  ->  GET user/bundles/indexlastchance
    | Needs: stock EXACTLY 1 (the controller uses where('stock', 1)),
    |        opening_time <= now, ended_time >= now
    ---------------------------------------------------------------- */
    private function lastChanceRows($faker, Carbon $now, object $branch, array $categoryIds): array
    {
        $rows = [];
        for ($i = 1; $i <= 4; $i++) {
            $rows[] = $this->bundle($faker, $now, $branch, $categoryIds, [
                'name'         => self::TAG . ' Last chance ' . $i,
                'stock'        => 1,   // exact match required, not "> 0"
                'opening_time' => $now->copy()->subHours(3),
                'ended_time'   => $now->copy()->addHours(5),
            ]);
        }

        return $rows;
    }

    /* ---------------------------------------------------------------
    | Before it is too late  ->  GET user/bundles/indexlasthour
    | Needs: ended_time > now AND ended_time <= now + 1 hour.
    | Note this endpoint does NOT filter on stock or opening_time.
    ---------------------------------------------------------------- */
    private function lastHourRows($faker, Carbon $now, object $branch, array $categoryIds): array
    {
        $rows = [];
        // Spread inside the window so the section stays populated for a while,
        // but keep every one strictly under +60 minutes.
        foreach ([20, 35, 50] as $i => $minutes) {
            $rows[] = $this->bundle($faker, $now, $branch, $categoryIds, [
                'name'         => self::TAG . ' Last hour ' . ($i + 1),
                'stock'        => random_int(1, 5),
                'opening_time' => $now->copy()->subHours(4),
                'ended_time'   => $now->copy()->addMinutes($minutes),
            ]);
        }

        return $rows;
    }

    /* ---------------------------------------------------------------
    | For tomorrow breakfast  ->  GET user/bundles/indexTomorrowBundles
    | Needs: DATE(opening_time) == tomorrow's date.
    ---------------------------------------------------------------- */
    private function tomorrowRows($faker, Carbon $now, object $branch, array $categoryIds): array
    {
        $tomorrow = $now->copy()->addDay();

        $rows = [];
        for ($i = 1; $i <= 4; $i++) {
            $rows[] = $this->bundle($faker, $now, $branch, $categoryIds, [
                'name'  => self::TAG . ' Tomorrow ' . $i,
                'stock' => random_int(3, 20),
                // Breakfast window on tomorrow's date - the filter only checks the date part.
                'opening_time' => $tomorrow->copy()->setTime(7, 0),
                'ended_time'   => $tomorrow->copy()->setTime(11, 0),
            ]);
        }

        return $rows;
    }

    /* -------------------------------------------------------------- */

    private function bundle($faker, Carbon $now, object $branch, array $categoryIds, array $overrides): array
    {
        $price = $faker->randomFloat(2, 10, 250);

        $row = array_merge([
            'company_id'           => $branch->company_id,
            'branch_id'            => $branch->id,
            'category_id'          => $categoryIds ? $faker->randomElement($categoryIds) : null,
            'image'                => null,
            'description'          => $faker->sentence(),
            'price'                => $price,
            'price_after_discount' => round($price * 0.6, 2),
            'active'               => true,
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $overrides);

        $row['opening_time'] = $row['opening_time']->format('Y-m-d H:i:s');
        $row['ended_time']   = $this->formatEnded($row['ended_time']);

        return $row;
    }

    /**
     * ended_time is declared as TIME in the bundles migration, but the controllers
     * compare it against full datetimes. Write whichever form the live column takes
     * so both the TIME schema and an already-altered DATETIME schema work.
     */
    private function formatEnded(Carbon $value): string
    {
        return $this->endedTimeIsDateTime()
            ? $value->format('Y-m-d H:i:s')
            : $value->format('H:i:s');
    }

    private function endedTimeIsDateTime(): bool
    {
        if ($this->endedIsDateTime !== null) {
            return $this->endedIsDateTime;
        }

        $type = strtolower((string) Schema::getColumnType('bundles', 'ended_time'));

        return $this->endedIsDateTime = str_contains($type, 'datetime')
            || str_contains($type, 'timestamp');
    }

    private function resolveBranch(): ?object
    {
        if (! Schema::hasTable('branches')) {
            return null;
        }

        return DB::table('branches')->orderBy('id')->first(['id', 'company_id']);
    }

    private function clearOwnedBundles(): void
    {
        DB::table('bundles')->where('name', 'LIKE', self::TAG . '%')->delete();
    }

    /** Re-runs each endpoint filter against the DB and prints what the app will see. */
    private function report(Carbon $now): void
    {
        $nextHour = $now->copy()->addHour();
        $tomorrow = $now->copy()->addDay()->toDateString();

        $counts = [
            'user/bundles' => DB::table('bundles')
                ->where('active', true)->where('stock', '>', 0)
                ->where('opening_time', '<=', $now->format('Y-m-d H:i:s'))
                ->where('ended_time', '>=', $this->formatEnded($now))
                ->count(),
            'user/bundles/indexlastchance' => DB::table('bundles')
                ->where('active', true)->where('stock', 1)
                ->where('opening_time', '<=', $now->format('Y-m-d H:i:s'))
                ->where('ended_time', '>=', $this->formatEnded($now))
                ->count(),
            'user/bundles/indexlasthour' => DB::table('bundles')
                ->where('active', true)
                ->where('ended_time', '>', $this->formatEnded($now))
                ->where('ended_time', '<=', $this->formatEnded($nextHour))
                ->count(),
            'user/bundles/indexTomorrowBundles' => DB::table('bundles')
                ->where('active', true)
                ->whereDate('opening_time', $tomorrow)
                ->count(),
            'user/categories' => DB::table('categories')->count(),
        ];

        foreach ($counts as $endpoint => $count) {
            $line = str_pad($endpoint, 36) . $count . ' row(s)';
            $count > 0
                ? $this->command?->info('  OK    ' . $line)
                : $this->command?->warn('  EMPTY ' . $line);
        }
    }
}
