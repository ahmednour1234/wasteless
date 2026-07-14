<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\StoreRecommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StoreRecommendationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'store_name'    => 'required|string|max:255',
            'location_hint' => 'nullable|string|max:255',
            'notes'         => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        StoreRecommendation::create([
            'customer_id'   => Auth::id(),
            'store_name'    => $request->input('store_name'),
            'location_hint' => $request->input('location_hint'),
            'notes'         => $request->input('notes'),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Store recommendation submitted successfully',
        ], 201);
    }
}
