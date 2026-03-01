<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Favourite;
use App\Models\Bundle;
use App\Models\Customer;
use App\Http\Resources\FavouriteResource;

class FavouriteController extends Controller
{
    /**
     * الحصول على معرف العميل المرتبط بالمستخدم الحالي
     */
    protected function getCustomerId()
    {
        $customer = Customer::where('id', Auth::id())->first();

        if (!$customer) {
            abort(response()->json([
                'status'  => false,
                'message' => 'لا يوجد حساب عميل مرتبط بالمستخدم.',
            ], 404));
        }

        return $customer->id;
    }

    /**
     * عرض كل المفضلات للعميل الحالي
     */
    public function index(Request $request)
    {
        $customerId = $this->getCustomerId();

        $favourites = Favourite::where('user_id', $customerId)
            ->with('bundle')
            ->get();

        return FavouriteResource::collection($favourites);
    }

    /**
     * إضافة بندل إلى المفضلة
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bundle_id' => 'required|exists:bundles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $customerId = $this->getCustomerId();

        $favourite = Favourite::firstOrCreate([
            'user_id'   => $customerId,
            'bundle_id' => $request->bundle_id,
        ]);

        $favourite->load('bundle');

        return new FavouriteResource($favourite);
    }

    /**
     * عرض مفضلة واحدة بناءً على bundle_id
     */
    public function show($bundleId)
    {
        $customerId = $this->getCustomerId();
        $favourite = Favourite::where('user_id', $customerId)
            ->where('bundle_id', $bundleId)
            ->with('bundle')
            ->first();

        if (!$favourite) {
            return response()->json(['message' => 'المفضلة غير موجودة.'], 404);
        }

        return new FavouriteResource($favourite);
    }

    /**
     * حذف بندل من المفضلة
     */
    public function destroy(Request $request, $bundleId)
    {
        $customerId = $this->getCustomerId();

        $deleted = Favourite::where('user_id', $customerId)
            ->where('bundle_id', $bundleId)
            ->delete();

        if ($deleted > 0) {
            return response()->json([
                'status'  => true,
                'message' => 'تم الحذف بنجاح.',
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'المفضلة غير موجودة أو سبق حذفها.',
            ], 404);
        }
    }
}
