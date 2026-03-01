<?php
// app/Http/Controllers/API/ReviewController.php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Bundle;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index(Bundle $bundle)
    {
        $reviews = $bundle->reviews()->latest()->paginate(10);
        return ReviewResource::collection($reviews);
    }
    public function store(Request $request, Bundle $bundle)
    {
        $v = Validator::make($request->all(), [
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $review = Review::create([
            'bundle_id'   => $bundle->id,
            'customer_id' => $request->user()->id,
            'bundle_data' => $bundle->only('name','price','price_after_discount'),
            'rating'      => $request->rating,
            'comment'     => $request->comment,
        ]);

        return new ReviewResource($review);
    }
    public function show(Review $review)
    {
        return new ReviewResource($review);
    }
}
