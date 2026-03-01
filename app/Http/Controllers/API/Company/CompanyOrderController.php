<?php

namespace App\Http\Controllers\API\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderDetail;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CompanyOrderDetailResource;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Validator;
use DB;
class CompanyOrderController extends Controller
{
    // جلب كل order_details المرتبطة بالشركة الحالية
    public function index(Request $request)
    {
        $companyId = Auth::guard('company')->id();

        $details = OrderDetail::with(['bundle', 'order'])
            ->where('company_id', $companyId)
            ->latest()
            ->get();

        return CompanyOrderDetailResource::collection($details);
    }

    // جلب تفاصيل الطلب الواحد ولكن فقط ما يخص هذه الشركة
    public function show(Request $request, $orderId)
    {
        $companyId = Auth::guard('company')->id();

        $details = OrderDetail::with(['bundle', 'order'])
            ->where('company_id', $companyId)
            ->where('order_id', $orderId)
            ->get();

        if ($details->isEmpty()) {
            return response()->json(['message' => 'لا توجد بيانات'], 404);
        }

        return CompanyOrderDetailResource::collection($details);
    }

    // تحديث حالة order_detail واحدة
    public function updateStatus(Request $request, $detailId)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,shipped,delivered,canceled'
        ]);

        $companyId = Auth::guard('company')->id();

        $detail = OrderDetail::where('company_id', $companyId)->findOrFail($detailId);
        $detail->status = $request->status;
        $detail->save();

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث الحالة بنجاح',
            'data'    => new CompanyOrderDetailResource($detail->load('bundle', 'order')),
        ]);
    }
 public function updateStatusOrder(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:pending,confirmed,shipped,delivered,canceled'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $companyId = Auth::guard('company')->id();

    $detail = Order::findOrFail($id);

    // تحقق اختياري إذا أردت التأكد أن الطلب يخص هذه الشركة
    // if ($detail->company_id != $companyId) {
    //     return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
    // }

    $detail->status = $request->status;
    $detail->save();

    // تحديث كل الصفوف المرتبطة بهذا الطلب في جدول order_details
    \DB::table('order_details')
        ->where('order_id', $id)
        ->update(['status' => $request->status]);

    return response()->json([
        'status'  => true,
        'message' => 'تم تحديث الحالة بنجاح',
    ]);
}



}
