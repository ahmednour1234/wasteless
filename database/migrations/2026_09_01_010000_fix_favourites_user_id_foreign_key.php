<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * جدول favourites أُنشئ على السيرفر بنسخة قديمة من المايجريشن كان فيها:
 *     $table->foreignId('user_id')->constrained()
 * فاستنتج Laravel جدول `users` من اسم العمود، بينما القيمة المخزَّنة
 * فعليًا هي customers.id (FavouriteController يستخدم Customer::find).
 *
 * النتيجة: أي إضافة للمفضلة لعميل رقمه غير موجود في users ترجع خطأ 500
 *     SQLSTATE[23000] ... favourites_user_id_foreign ... REFERENCES `users`
 *
 * المايجريشن الأصلية محمية بـ if (! Schema::hasTable('favourites'))
 * فلن تُصلح السيرفر أبدًا، لذلك نصلح المفتاح هنا.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('favourites') || ! Schema::hasTable('customers')) {
            return;
        }

        // SQLite لا يدعم تعديل المفاتيح الأجنبية، والجداول الجديدة عليه
        // تُنشأ صحيحة من المايجريشن الأصلية.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1) احذف الصفوف اليتيمة التي لا تقابل عميلًا موجودًا،
        //    وإلا سيرفض MySQL إنشاء المفتاح الجديد.
        DB::statement('
            DELETE f FROM favourites f
            LEFT JOIN customers c ON c.id = f.user_id
            WHERE c.id IS NULL
        ');

        // 2) استبدل المفتاح القديم (users) بمفتاح يشير إلى customers.
        $foreignKeys = DB::select('
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = "favourites"
              AND COLUMN_NAME = "user_id"
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ');

        foreach ($foreignKeys as $key) {
            DB::statement("ALTER TABLE `favourites` DROP FOREIGN KEY `{$key->name}`");
        }

        DB::statement('
            ALTER TABLE `favourites`
            ADD CONSTRAINT `favourites_user_id_foreign`
            FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`)
            ON DELETE CASCADE
        ');
    }

    public function down(): void
    {
        // لا نعيد المفتاح الخاطئ.
    }
};
