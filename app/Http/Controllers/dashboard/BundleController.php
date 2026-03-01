<?php
// app/Http/Controllers/Dashboard/BundleController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Branch;
use App\Models\Category;
use App\Helpers\FileHelper;
use App\Models\Company;
use Illuminate\Http\Request;
use Carbon\Carbon;           // لاستخدام دوال التاريخ

class BundleController extends Controller
{
    /**
     * قائمة الباندلز مع جميع الفلاتر المطلوبة
     */
    public function index(Request $request)
    {
         $permissions = session('permissions');
    if (!isset($permissions['Bundle']) || !in_array('read', $permissions['Bundle']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
            $companies = \App\Models\Company::select('id', 'name')->orderBy('name')->get();

        $bundles = Bundle::query()

            /*— 1) الفلتر بحسب الشركة —*/
            ->when($request->filled('company_id'), fn ($q) =>
                $q->where('company_id', $request->company_id))

            /*— 2) الفلتر بالاسم (بحث جزئي) —*/
            ->when($request->filled('name'), fn ($q) =>
                $q->where('name', 'LIKE', '%' . $request->name . '%'))

            /*— 3) الفلتر بالسعر (بين حدّين) —*/
            ->when($request->filled('price_from') || $request->filled('price_to'), function ($q) use ($request) {
                $from = $request->price_from ?? 0;
                $to   = $request->price_to   ?? PHP_INT_MAX;
                $q->whereBetween('price', [$from, $to]);
            })

            /*— 4) هل يوجد خصم؟ —*/
            ->when($request->has('has_discount'), function ($q) use ($request) {
                if ($request->boolean('has_discount')) {
                    // السعر بعد الخصم أصغر من السعر الأصلي
                    $q->whereColumn('price_after_discount', '<', 'price');
                } else {
                    $q->where(function ($q) {
                        $q->whereColumn('price_after_discount', '>=', 'price')
                          ->orWhereNull('price_after_discount');
                    });
                }
            })

            /*— 5) نشط / غير نشط —*/
            ->when($request->filled('active'), fn ($q) =>
                $q->where('active', $request->boolean('active')))

            /*— 6) فترة الفتح —*/
            ->when($request->filled('opening_from') || $request->filled('opening_to'), function ($q) use ($request) {
                $from = $request->opening_from ? Carbon::parse($request->opening_from)->startOfDay() : null;
                $to   = $request->opening_to   ? Carbon::parse($request->opening_to)->endOfDay()   : null;
                $q->whereBetween('opening_time', array_filter([$from, $to]));
            })

            /*— 7) فترة الإغلاق —*/
            ->when($request->filled('ended_from') || $request->filled('ended_to'), function ($q) use ($request) {
                $from = $request->ended_from ? Carbon::parse($request->ended_from)->startOfDay() : null;
                $to   = $request->ended_to   ? Carbon::parse($request->ended_to)->endOfDay()   : null;
                $q->whereBetween('ended_time', array_filter([$from, $to]));
            })

            /*— 8) فلترة بتاريخ الإنشاء —*/
            ->when($request->filled('created_from') || $request->filled('created_to'), function ($q) use ($request) {
                $from = $request->created_from ? Carbon::parse($request->created_from)->startOfDay() : null;
                $to   = $request->created_to   ? Carbon::parse($request->created_to)->endOfDay()   : null;
                $q->whereBetween('created_at', array_filter([$from, $to]));
            })

            /*— الترتيب الأحدث فالأقدم + ترحيل الصفحة —*/
            ->latest()
            ->paginate(10)
            ->appends($request->query());   // يحافظ على معايير البحث أثناء الانتقال بين الصفحات

        return view('content.bundles.index', compact('bundles','companies'));
    }

    /**
     * عرض تفاصيل Bundle مفرد
     */
  public function show(Bundle $bundle)
{
    /* ----- صلاحيات لوحة التحكم ----- */
    $permissions = session('permissions');
    if (
        !isset($permissions['Bundle']) ||
        !in_array('read', $permissions['Bundle']['actions'])
    ) {
        abort(403, 'Unauthorized action.');
    }

    /* ----- تحميل التقييمات (آخر 10 مثلاً) ----- */
    // نحمّل العلاقة مع العميل لتجنّب N+1
    $reviews = $bundle->reviews()
                      ->with('customer:id,name')
                      ->latest()
                      ->paginate(10);   // غيّر الرقم أو أزِل paginate إذا أردت كلّها

    /* ----- عرض الصفحة ----- */
    return view('content.bundles.show', compact('bundle', 'reviews'));
}
  public function create()
    {
        $permissions = session('permissions');
        if (!isset($permissions['Bundle']) || !in_array('create', $permissions['Bundle']['actions'])) {
            abort(403, 'Unauthorized action.');
        }

        // جلب جميع الشركات والتصنيفات
        $companies  = Company::select('id', 'name')->orderBy('name')->get();
        $categories = Category::select('id', 'name')->orderBy('name')->get();

        // يمكن جلب كل الفروع، أو جلبها لاحقًا عبر AJAX بناءً على الشركة
        $branches = Branch::select('id', 'company_id', 'name')->orderBy('name')->get();

        return view('content.bundles.create', compact('companies', 'categories', 'branches'));
    }

    /**
     * تخزين Bundle جديد
     */
  public function store(Request $request)
    {
        $permissions = session('permissions');
        if (!isset($permissions['Bundle']) || !in_array('create', $permissions['Bundle']['actions'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'company_id'           => 'required|exists:companies,id',
            'category_id'          => 'required|exists:categories,id',
            'branch_id'            => 'required|exists:branches,id',
            'price'                => 'required|numeric|min:0',
            'price_after_discount' => 'nullable|numeric|min:0|lte:price',
            'stock'                => 'required|integer|min:0',
            'opening_time'         => 'required|date',
            'ended_time'           => 'required|date|after_or_equal:opening_time',
            'active'               => 'required|boolean',
            'image'                => 'nullable|image|max:2048', // صورة بحد أقصى 2 ميجا
        ]);

        // رفع الصورة إذا وُجدت
        if ($request->hasFile('image')) {
            $validated['image'] = FileHelper::uploadImage(
                $request->file('image'),
                'uploads/bundles'
            );
        }

        // إنشاء السجل
        Bundle::create([
            'name'                 => $validated['name'],
            'description'          => $validated['description'] ?? null,
            'company_id'           => $validated['company_id'],
            'category_id'          => $validated['category_id'],
            'branch_id'            => $validated['branch_id'],
            'price'                => $validated['price'],
            'price_after_discount' => $validated['price_after_discount'] ?? null,
            'stock'                => $validated['stock'],
            'opening_time'         => $validated['opening_time'],
            'ended_time'           => $validated['ended_time'],
            'active'               => $validated['active'],
            'image'                => $validated['image'] ?? null,
        ]);

        return redirect()
            ->route('bundels.index')
            ->with('success', 'Bundle created successfully.');
    }


    /**
     * تفعيل / إلغاء تفعيل Bundle (AJAX)
     */
    public function toggle(Bundle $bundle)
    {
          $permissions = session('permissions');
    if (!isset($permissions['Bundle']) || !in_array('write', $permissions['Bundle']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        $bundle->update(['active' => ! $bundle->active]);

        return response()->json([
            'status' => $bundle->active,
        ]);
    }
}
