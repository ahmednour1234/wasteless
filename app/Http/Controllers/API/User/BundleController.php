<?php
// app/Http/Controllers/API/User/BundleController.php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BundleResource;   // أو Public\BundleResource
use App\Models\Bundle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BundleController extends Controller
{
    /*-------------------------------------------------
    | 1)  قائمة عامة للباندلز
    --------------------------------------------------*/
 public function index(Request $request): JsonResponse
{
$now = Carbon::now('Africa/Cairo');

// 2) بناء الاستعلام مع الفلاتر المطلوبة
$query = Bundle::active()
    ->where('stock', '>', 0)
    ->where('opening_time', '<=', $now)
    ->where('ended_time', '>=', $now)
    ->with(['company', 'branch', 'category', 'reviews.customer']);
    /* فلاتر اختيارية */
    $query
        ->when($request->filled('name'), fn($q) =>
            $q->where('name', 'LIKE', "%{$request->name}%"))
        ->when($request->filled('price_min'), fn($q) =>
            $q->where('price', '>=', $request->price_min))
        ->when($request->filled('price_max'), fn($q) =>
            $q->where('price', '<=', $request->price_max))
        ->when($request->filled('has_discount'), fn($q) =>
            $request->boolean('has_discount')
                ? $q->whereNotNull('price_after_discount')->where('price_after_discount', '>', 0)
                : $q->where(function ($q2) {
                    $q2->whereNull('price_after_discount')
                       ->orWhere('price_after_discount', 0);
                }))
        ->when($request->filled('date_from'), fn($q) =>
            $q->whereDate('created_at', '>=', $request->date_from))
        ->when($request->filled('date_to'), fn($q) =>
            $q->whereDate('created_at', '<=', $request->date_to))

        // ✅ فلتر حسب category_id
        ->when($request->filled('category_id'), fn($q) =>
            $q->where('category_id', $request->category_id));

    $bundles = $query->latest()->paginate($request->get('per_page', 10));

    return response()->json(
        BundleResource::collection($bundles)
            ->additional(['message' => 'Public bundles list'])
    );
}
public function indexlastchance(Request $request): JsonResponse
{
    $now = Carbon::now('Africa/Cairo');

    $query = Bundle::active()->where('stock', 1)->where('opening_time', '<=', $now)->where('ended_time', '>=', $now)
        ->with(['company', 'branch', 'category', 'reviews.customer']);

    $query
        ->when($request->filled('name'), fn($q) =>
            $q->where('name', 'LIKE', "%{$request->name}%"))
        ->when($request->filled('price_min'), fn($q) =>
            $q->where('price', '>=', $request->price_min))
        ->when($request->filled('price_max'), fn($q) =>
            $q->where('price', '<=', $request->price_max))
        ->when($request->filled('has_discount'), fn($q) =>
            $request->boolean('has_discount')
                ? $q->whereNotNull('price_after_discount')->where('price_after_discount', '>', 0)
                : $q->where(function ($q2) {
                    $q2->whereNull('price_after_discount')
                       ->orWhere('price_after_discount', 0);
                }))
        ->when($request->filled('date_from'), fn($q) =>
            $q->whereDate('created_at', '>=', $request->date_from))
        ->when($request->filled('date_to'), fn($q) =>
            $q->whereDate('created_at', '<=', $request->date_to))
        ->when($request->filled('category_id'), fn($q) =>
            $q->where('category_id', $request->category_id))

        // ✅ فلتر فقط التي كميتها = 1
        ->when($request->boolean('has_one_quantity'), fn($q) =>
            $q->where('stock', 1)); // أو 'quantity' حسب اسم العمود

    $bundles = $query->latest()->paginate($request->get('per_page', 10));

    return response()->json(
        BundleResource::collection($bundles)
            ->additional(['message' => 'Public bundles list'])
    );
}

public function indexlasthour(Request $request): JsonResponse
{
    $now = now();
    $nextHour = $now->copy()->addHour();

    $query = Bundle::active()
        ->with(['company', 'branch', 'category', 'reviews.customer'])
        ->where('ended_time', '>', $now)
        ->where('ended_time', '<=', $nextHour);

    $bundles = $query->latest()->paginate($request->get('per_page', 10));

    return response()->json(
        BundleResource::collection($bundles)
            ->additional(['message' => 'Bundles ending within the next hour'])
    );
}
public function indexTomorrowBundles(Request $request): JsonResponse
{
    $tomorrow = now()->addDay()->startOfDay();         // بداية يوم غدًا
    $endOfTomorrow = now()->addDay()->endOfDay();       // نهاية يوم غدًا

    $query = Bundle::active()
        ->with(['company', 'branch', 'category', 'reviews.customer'])
        ->whereDate('opening_time', '=', $tomorrow->toDateString());

    $bundles = $query->latest()->paginate($request->get('per_page', 10));

    return response()->json(
        BundleResource::collection($bundles)
            ->additional(['message' => 'Bundles starting and ending tomorrow'])
    );
}



    /*-------------------------------------------------
    | 2)  عرض Bundle واحد
    --------------------------------------------------*/
    public function show(Bundle $bundle): JsonResponse
    {
        // السماح بالعرض فقط إذا كان نشطاً
        abort_unless($bundle->active, 404);

        return response()->json(new BundleResource($bundle->with(['company','branch','category','reviews.customer'])->find($bundle->id)), 200);
    }
}
