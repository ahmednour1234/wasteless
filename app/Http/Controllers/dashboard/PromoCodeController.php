<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PromoCode::query()
            ->withCount('usages')
            ->withSum('usages', 'discount_amount');

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->active === '1' || $request->active === '0') {
            $query->where('is_active', $request->active);
        }

        $promoCodes = $query->latest()->paginate(15)->withQueryString();

        return view('content.promo_codes.index', compact('promoCodes'));
    }

    public function create()
    {
        return view('content.promo_codes.create', ['promoCode' => new PromoCode(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        PromoCode::create($this->validated($request));

        return redirect()->route('promo-codes.index')->with('success', 'Promo code created successfully');
    }

    public function show(PromoCode $promoCode)
    {
        $usages = $promoCode->usages()->with(['customer', 'order'])->latest()->paginate(20);

        return view('content.promo_codes.show', compact('promoCode', 'usages'));
    }

    public function edit(PromoCode $promoCode)
    {
        return view('content.promo_codes.edit', compact('promoCode'));
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $promoCode->update($this->validated($request, $promoCode));

        return redirect()->route('promo-codes.index')->with('success', 'Promo code updated successfully');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return redirect()->route('promo-codes.index')->with('success', 'Promo code deleted successfully');
    }

    public function toggle(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => !$promoCode->is_active]);

        return response()->json(['status' => $promoCode->is_active]);
    }

    private function validated(Request $request, ?PromoCode $promoCode = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->code))]);

        $data = $request->validate([
            'code'                 => ['required', 'string', 'max:50', Rule::unique('promo_codes', 'code')->ignore($promoCode?->id)],
            'type'                 => 'required|in:percentage,fixed',
            'value'                => ['required', 'numeric', 'min:0.01', $request->type === 'percentage' ? 'max:100' : 'max:1000000'],
            'max_discount'         => 'nullable|numeric|min:0',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'start_date'           => 'nullable|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
