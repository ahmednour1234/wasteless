<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // --- متوسط التقييم ---
        $avgRating = round($this->reviews()->avg('rating') ?? 0, 1);

        // --- عدد التقييمات ---
        $reviewsCount = $this->reviews()->count();
    $now = Carbon::now();
$nowInCairo = Carbon::now('Africa/Cairo');

    $opening=Carbon::parse($this->opening_time);
    $ended = Carbon::parse($this->ended_time);

    // إذا لم ينتهِ بعد، احسب الفرق بالدقائق وإلا 0
    $minutesLeft = $ended->isFuture()
        ? $now->diffInMinutes($ended)
        : 0;
        return [
            'now'=>$now,
            'id'                   => $this->id,
            'name'                 => $this->name,
            'image'                => $this->image ? asset($this->image) : null,
            'description'          => $this->description,
            'price'                => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'stock'                => $this->stock,
            'opening_time'         => $this->opening_time,
            'ended_time'           => $ended,
                   'minutes_left'   => $minutesLeft,
        // مثال: "5 دقائق" أو "انتهت"
        'time_left_text' => $minutesLeft > 0
            ? $minutesLeft . ' دقيقة'
            : 'انتهت',
            'active'               => (bool) $this->active,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,

            /* --- Company --- */
            'company' => [
                'id'    => $this->company_id,
                'name'  => $this->company->name ?? null,
                'image' => $this->company->img ? asset($this->company->img) : null,
            ],

            /* --- Branch --- */
            'branch' => [
                'id'   => $this->branch_id,
                'name' => $this->branch->name ?? null,
                                'address' => $this->branch->address ?? null,
                'lat'  => $this->branch->lat ?? null,
                'lng'  => $this->branch->lng ?? null,
            ],

            /* --- Category --- */
            'category' => [
                'id'   => $this->category_id,
                'name' => $this->category->name ?? null,
            ],

            /* --- Reviews --- */
            'rating_percentage' => $avgRating, // من 0 إلى 5
            'reviews_count'     => $reviewsCount,
            'latest_reviews'    => ReviewResource::collection(
                $this->whenLoaded('reviews', fn () =>
                    $this->reviews->sortByDesc('id')->take(8)
                )
            ),
        ];
    }
}
