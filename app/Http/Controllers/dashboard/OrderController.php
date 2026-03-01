<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Bundle;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Category;

class OrderController extends Controller
{
    // عرض كل الطلبات مع الفلاتر
    public function index(Request $request)
    {
        $orders = Order::with(['details' => function ($query) use ($request) {
            $query->when($request->bundle_id, fn($q) =>
                $q->where('bundle_id', $request->bundle_id));
            $query->when($request->company_id, fn($q) =>
                $q->where('company_id', $request->company_id));
            $query->when($request->branch_id, fn($q) =>
                $q->where('branch_id', $request->branch_id));
            $query->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id));
            $query->when($request->quantity, fn($q) =>
                $q->where('quantity', $request->quantity));
        }])->latest()->paginate(15);

        // إرسال الفلاتر للواجهة
        $bundles   = Bundle::all();
        $companies = Company::all();
        $branches  = Branch::all();
        $categories = Category::all();

        return view('content.orders.index', compact('orders', 'bundles', 'companies', 'branches', 'categories'));
    }

    // عرض تفاصيل طلب معين
    public function show($orderId)
    {
        $order = Order::with(['details.bundle'])->findOrFail($orderId);

        return view('content.orders.show', compact('order'));
    }
}
