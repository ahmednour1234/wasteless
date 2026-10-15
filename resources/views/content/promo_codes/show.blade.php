@extends('layouts/layoutMaster')

@section('title', 'Promo Code Usages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Promo Code: {{ $promoCode->code }}</h4>
    <a href="{{ route('promo-codes.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
<div class="table-responsive">
<table class="table align-middle text-center">
    <thead class="table-light">
        <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Discount</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($usages as $usage)
        <tr>
            <td>
                @if ($usage->order_id)
                    <a href="{{ route('orders.show', $usage->order_id) }}">#{{ $usage->order_id }}</a>
                @else
                    —
                @endif
            </td>
            <td>{{ $usage->customer->name ?? '—' }} <small class="text-muted d-block">{{ $usage->customer->phone ?? '' }}</small></td>
            <td class="text-danger">-{{ number_format($usage->discount_amount, 2) }} USD</td>
            <td>{{ $usage->created_at->format('Y-m-d H:i') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="4">This promo code has not been used yet.</td>
        </tr>
    @endforelse
    </tbody>
</table>
</div>
</div>

<div class="mt-3">
    {{ $usages->links('pagination::bootstrap-5') }}
</div>
@endsection
