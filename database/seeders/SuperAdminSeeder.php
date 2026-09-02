<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * أدمن اختبار بصلاحيات كاملة على كل موديولات الداشبورد.
 *
 * التشغيل:
 *     php artisan db:seed --class=SuperAdminSeeder
 *
 * بيانات الدخول:
 *     email:    superadmin@wasteless.test
 *     password: password
 *
 * ملاحظة مهمة: الكنترولرز بتقرأ الصلاحيات من session('permissions')
 * اللي بتتملى وقت تسجيل الدخول من roles.data (شوف LoginBasic).
 * يعني أي تعديل على الدور لازم يتبعه تسجيل خروج ودخول من جديد.
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * الموديولات كما تفحصها الكنترولرز حرفيًا عبر $permissions['<Module>'].
     * القائمة صريحة عن قصد: WastelessNewSeeder يستخرجها بـ regex من الكود،
     * وده بيفوّت بصمت أي كنترولر جديد بصيغة مختلفة.
     */
    public const MODULES = [
        'Branch',
        'Bundle',
        'Category',
        'Company',
        'Customer',
        'Pdfs',
        'Projects',
        'Review',
        'Setting Management',
        'User Management',
    ];

    /** الأفعال المستخدمة في in_array(...) داخل الكنترولرز. */
    public const ACTIONS = ['read', 'create', 'write', 'delete'];

    public const ROLE_NAME = 'Super Admin';
    public const EMAIL = 'superadmin@wasteless.test';
    public const PASSWORD = 'password';

    public function run(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('users')) {
            $this->command?->warn('جدول roles أو users غير موجود — تم تخطي SuperAdminSeeder.');

            return;
        }

        $permissions = [];
        foreach (self::MODULES as $module) {
            $permissions[$module] = ['actions' => self::ACTIONS];
        }

        $role = Role::updateOrCreate(
            ['name' => self::ROLE_NAME],
            ['data' => $permissions],
        );

        $user = User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Super Admin',
                'phone' => '+96170000000',
                'password' => Hash::make(self::PASSWORD),
                'role_id' => $role->id,
            ],
        );

        $this->command?->info('Super Admin جاهز:');
        $this->command?->line('  email:    ' . self::EMAIL);
        $this->command?->line('  password: ' . self::PASSWORD);
        $this->command?->line('  role_id:  ' . $role->id . ' (' . self::ROLE_NAME . ')');
        $this->command?->line('  modules:  ' . count(self::MODULES) . ' × ' . count(self::ACTIONS) . ' actions');

        // تحذير لو ظهر موديول في الكود مش موجود في القائمة أعلاه.
        $this->warnAboutUnlistedModules();

        unset($user);
    }

    /**
     * يفحص الكنترولرز بحثًا عن $permissions['X'] غير مدرجة في MODULES،
     * حتى لا يصبح الدور ناقصًا بصمت بعد إضافة كنترولر جديد.
     */
    private function warnAboutUnlistedModules(): void
    {
        $path = dirname(__DIR__, 2) . '/app/Http/Controllers';
        if (! is_dir($path)) {
            return;
        }

        $found = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            preg_match_all(
                '/\$permissions\[\s*[\'"]([^\'"]+)[\'"]\s*\]/',
                (string) file_get_contents($file->getPathname()),
                $matches
            );

            foreach ($matches[1] ?? [] as $module) {
                $found[$module] = true;
            }
        }

        $missing = array_diff(array_keys($found), self::MODULES);

        if (! empty($missing)) {
            $this->command?->warn(
                'موديولات موجودة في الكود وغير مدرجة في SuperAdminSeeder::MODULES: '
                . implode(', ', $missing)
            );
        }
    }
}
