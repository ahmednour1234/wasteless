<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Database\Seeders\WastelessNewSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SeederController extends Controller
{
    /**
     * POST /api/admin/seed
     *
     * Runs WastelessNewSeeder to reset and re-populate all tables.
     * Protected by the X-Seed-Secret header (must match SEED_API_SECRET in .env).
     *
     * Only available when APP_ENV is local or staging.
     */
    public function run(Request $request)
    {
        // ── environment guard ───────────────────────────────────────────────
        if (! in_array(app()->environment(), ['local', 'staging'])) {
            return response()->json(['message' => 'Not available in this environment.'], 403);
        }

        // ── secret header guard ─────────────────────────────────────────────
        $secret = config('app.seed_api_secret');
        if (! $secret || $request->header('X-Seed-Secret') !== $secret) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        try {
            Artisan::call('db:seed', ['--class' => WastelessNewSeeder::class, '--force' => true]);

            return response()->json([
                'message' => 'Database seeded successfully.',
                'output'  => Artisan::output(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Seeding failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
