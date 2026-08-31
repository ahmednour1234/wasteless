<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ended_time كان عمود TIME (وقت بدون تاريخ)، وده بيكسر أي مقارنة
 * بتاريخ ووقت كامل مثل:
 *   where('ended_time', '>', now())      // في indexlasthour
 *   where('ended_time', '>=', now())     // في index / indexlastchance
 * لأن المقارنة بتتم كنص: '01:56:15' > '2026-09-01 01:44:12' = false دائماً.
 *
 * التحويل لـ datetime بيخلي الفلاتر دي تشتغل صح.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bundles')) {
            return;
        }

        // نركّب الوقت المخزَّن على تاريخ opening_time لتكوين datetime كامل،
        // ولو وقت الإغلاق أبكر من الفتح تبقى الفترة ممتدة لليوم التالي.
        Schema::table('bundles', function ($table) {
            $table->dateTime('ended_time_new')->nullable()->after('ended_time');
        });

        foreach (DB::table('bundles')->select('id', 'opening_time', 'ended_time')->cursor() as $row) {
            if (! $row->ended_time || ! $row->opening_time) {
                continue;
            }

            $opening = \Illuminate\Support\Carbon::parse($row->opening_time);
            $time    = \Illuminate\Support\Carbon::parse($row->ended_time);

            $ended = $opening->copy()->setTime(
                (int) $time->format('H'),
                (int) $time->format('i'),
                (int) $time->format('s')
            );

            if ($ended->lessThan($opening)) {
                $ended->addDay();
            }

            DB::table('bundles')->where('id', $row->id)->update([
                'ended_time_new' => $ended->toDateTimeString(),
            ]);
        }

        Schema::table('bundles', function ($table) {
            $table->dropColumn('ended_time');
        });

        Schema::table('bundles', function ($table) {
            $table->renameColumn('ended_time_new', 'ended_time');
        });
    }

    public function down(): void
    {
        // العودة لعمود TIME (سيفقد جزء التاريخ).
        Schema::table('bundles', function ($table) {
            $table->time('ended_time_old')->nullable()->after('ended_time');
        });

        foreach (DB::table('bundles')->select('id', 'ended_time')->cursor() as $row) {
            if (! $row->ended_time) {
                continue;
            }
            DB::table('bundles')->where('id', $row->id)->update([
                'ended_time_old' => \Illuminate\Support\Carbon::parse($row->ended_time)->format('H:i:s'),
            ]);
        }

        Schema::table('bundles', function ($table) {
            $table->dropColumn('ended_time');
        });

        Schema::table('bundles', function ($table) {
            $table->renameColumn('ended_time_old', 'ended_time');
        });
    }
};
