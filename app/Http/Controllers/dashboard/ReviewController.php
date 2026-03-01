<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /* ===== list all reviews (+ search) ===== */
    public function index(Request $request)
    {
         $permissions = session('permissions');
    if (!isset($permissions['Review']) || !in_array('read', $permissions['Review']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        $query = Review::with(['bundle', 'customer:id,name']);

        if ($s = $request->q) {
            $query->whereHas('customer', fn($q) => $q->where('name', 'LIKE', "%$s%"))
                  ->orWhereHas('bundle',   fn($q) => $q->where('name', 'LIKE', "%$s%"));
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();
        return view('content.reviews.index', compact('reviews'));
    }

    /* ===== single review ===== */
    public function show(Review $review)
    {
         $permissions = session('permissions');
    if (!isset($permissions['Review']) || !in_array('read', $permissions['Review']['actions'])) {
        abort(403, 'Unauthorized action.');
    }
        $review->load(['bundle', 'customer']);
        return view('content.reviews.show', compact('review'));
    }

    /* ===== toggle active/inactive (AJAX or GET) ===== */
   public function toggle(Review $review)
{
    // صلاحيات لوحة التحكم
    $permissions = session('permissions');
    if (!isset($permissions['Review']) ||
        !in_array('write', $permissions['Review']['actions'])) {
        abort(403, 'Unauthorized action.');
    }

    // 1 → 0   |   0 → 1
    $review->update([
        'active' => $review->active ? 0 : 1
    ]);

    // استجابة مناسبة لنوع الطلب
    if (request()->wantsJson()) {
        return response()->json([
            'status'  => 'success',
            'active'  => (int) $review->active          // القيمة الجديدة
        ]);
    }

    return back()->with('success', 'Status updated.');
}

}
