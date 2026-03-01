<?php
// app/Http/Controllers/API/Company/BundleController.php

namespace App\Http\Controllers\API\Company;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Http\Resources\BundleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BundleController extends Controller
{
    /**
     * إنشاء Bundle جديد.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = Auth::guard('company')->id();

        $query = Bundle::with(['company','branch','category','reviews.customer'])->where('company_id', $companyId)
            /* فلترة بالاسم */
            ->when($request->filled('name'), fn($q) =>
                $q->where('name', 'LIKE', "%{$request->name}%"))

            /* فلترة بالتاريخ (created_at) */
            ->when($request->filled('date_from'), fn($q) =>
                $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) =>
                $q->whereDate('created_at', '<=', $request->date_to))

            /* فلترة بالسعر */
            ->when($request->filled('price_min'), fn($q) =>
                $q->where('price', '>=', $request->price_min))
            ->when($request->filled('price_max'), fn($q) =>
                $q->where('price', '<=', $request->price_max))

            /* فلترة بالمخزون */
            ->when($request->filled('stock_min'), fn($q) =>
                $q->where('stock', '>=', $request->stock_min))
            ->when($request->filled('stock_max'), fn($q) =>
                $q->where('stock', '<=', $request->stock_max))

            /* فلترة بوجود خصم */
            ->when($request->has('has_discount'), fn($q) =>
                $request->boolean('has_discount')
                    ? $q->whereNotNull('price_after_discount')->where('price_after_discount', '>', 0)
                    : $q->where(function($q2){ $q2->whereNull('price_after_discount')->orWhere('price_after_discount', 0); }))

            /* فلترة بالحالة النشطة */
            ->when($request->has('active'), fn($q) =>
                $q->where('active', $request->boolean('active')));

        $bundles = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json(BundleResource::collection($bundles)
            ->additional(['message' => 'Bundles list']), 200);
    }

    /*-------------------------------------------------
    | 2)  عرض Bundle واحد
    --------------------------------------------------*/
    public function show(Bundle $bundle): JsonResponse
    {
        if ($bundle->company_id !== Auth::guard('company')->id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json(new BundleResource($bundle->load(['company','category', 'branch', 'reviews.customer'])), 200);
    }
    public function store(Request $request): JsonResponse
    {
        // التحقق من البيانات
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'image'                => ['nullable', 'image'],
            'description'          => ['nullable', 'string'],
            'branch_id'            => ['required', 'exists:branches,id'],
            'category_id'            => ['nullable', 'exists:categories,id'],
            'price'                => ['required', 'numeric', 'min:0'],
            'price_after_discount' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'stock'                => ['required', 'integer', 'min:0'],
            'opening_time'         => ['required', 'date'],
            'ended_time'           => ['required', 'date', 'after:opening_time'],
            'active'               => ['sometimes', 'boolean'],
        ]);

        // إضافة company_id من حارس company
        $validated['company_id'] = Auth::guard('company')->id();

        // رفع الصورة (إن وُجدت) باستخدام FileHelper
        if ($request->hasFile('image')) {
            $validated['image'] = FileHelper::uploadImage(
                $request->file('image'),
                'uploads/bundles'     // مجلّد فرعي داخل /public/uploads
            );
        }

        // إنشاء السجل
        $bundle = Bundle::create($validated);

        return response()->json([
            'message' => 'Bundle created successfully.',
            'data'    => $bundle,
        ], 201);
    }

    /**
     * تحديث بيانات Bundle.
     */
    public function update(Request $request, Bundle $bundle): JsonResponse
    {
        // التأكد أن الـBundle يخص الشركة الحالية
        if ($bundle->company_id !== Auth::guard('company')->id()) {
            abort(403, 'Unauthorized');
        }

        // التحقق من البيانات
        $validated = $request->validate([
            'name'                 => ['sometimes', 'string', 'max:255'],
            'image'                => ['sometimes', 'image'],
            'description'          => ['sometimes', 'string'],
            'branch_id'            => ['sometimes', Rule::exists('branches', 'id')],
                        'category_id'            => ['sometimes', 'exists:categories,id'],

            'price'                => ['sometimes', 'numeric', 'min:0'],
            'price_after_discount' => ['sometimes', 'numeric', 'min:0', 'lte:price'],
            'stock'                => ['sometimes', 'integer', 'min:0'],
            'opening_time'         => ['sometimes', 'date'],
            'ended_time'           => ['sometimes', 'date', 'after:opening_time'],
            'active'               => ['sometimes', 'boolean'],
        ]);

        // تحديث الصورة إن وُجدت
        if ($request->hasFile('image')) {
            $validated['image'] = FileHelper::uploadImage(
                $request->file('image'),
                'uploads/bundles'
            );
        }

        // حفظ التغييرات
        $bundle->update($validated);

        return response()->json([
            'message' => 'Bundle updated successfully.',
            'data'    => $bundle,
        ]);
    }

    /**
     * تبديل حالة التفعيل ↔️.
     */
    public function toggle(Bundle $bundle): JsonResponse
    {
        if ($bundle->company_id !== Auth::guard('company')->id()) {
            abort(403, 'Unauthorized');
        }

        $bundle->active = ! $bundle->active;
        $bundle->save();

        return response()->json([
            'status'  => $bundle->active,
            'message' => $bundle->active ? 'Bundle activated.' : 'Bundle deactivated.',
        ]);
    }
}
