@extends('layouts/layoutMaster')

@section('title', 'Promo Codes')

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-status').forEach(el => {
        el.addEventListener('change', e => {
            const input = e.target;
            fetch(input.dataset.url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error(res.status);
                return res.json();
            })
            .then(data => { input.checked = !!data.status; })
            .catch(() => {
                input.checked = !input.checked;
                alert('Failed to update status');
            });
        });
    });
});
</script>
@endsection

@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Promo Codes</h4>
    <a class="btn btn-primary" href="{{ route('promo-codes.create') }}">Add New Promo Code</a>
</div>

<form method="GET" class="card p-3 mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="code">Code contains</label>
            <input type="text" name="code" id="code" value="{{ request('code') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="active">Status</label>
            <select name="active" id="active" class="form-select">
                <option value="">Any</option>
                <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary w-100">Filter</button>
            <a href="{{ route('promo-codes.index') }}" class="btn btn-light w-100">Reset</a>
        </div>
    </div>
</form>

<div class="card">
<div class="table-responsive">
<table class="table align-middle text-center">
    <thead class="table-light">
        <tr>
            <th>Code</th>
            <th>Discount</th>
            <th>Period</th>
            <th>Used / Limit</th>
            <th>Per User</th>
            <th>Total Discounted</th>
            <th>Active?</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($promoCodes as $promo)
        <tr>
            <td><strong>{{ $promo->code }}</strong></td>
            <td>
                @if ($promo->type === 'percentage')
                    {{ rtrim(rtrim(number_format($promo->value, 2), '0'), '.') }}%
                    @if ($promo->max_discount) <small class="text-muted d-block">max {{ number_format($promo->max_discount, 2) }} USD</small> @endif
                @else
                    {{ number_format($promo->value, 2) }} USD
                @endif
            </td>
            <td>
                <small>
                    {{ $promo->start_date?->format('Y-m-d H:i') ?? '—' }}<br>
                    {{ $promo->end_date?->format('Y-m-d H:i') ?? '—' }}
                </small>
                @if ($promo->end_date && $promo->end_date->isPast())
                    <span class="badge bg-label-danger d-block mt-1">Expired</span>
                @endif
            </td>
            <td>{{ $promo->usages_count }} / {{ $promo->usage_limit ?? '∞' }}</td>
            <td>{{ $promo->usage_limit_per_user ?? '∞' }}</td>
            <td class="text-danger">{{ number_format($promo->usages_sum_discount_amount ?? 0, 2) }} USD</td>
            <td>
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input toggle-status" type="checkbox"
                           data-url="{{ route('promo-codes.toggle', $promo) }}" {{ $promo->is_active ? 'checked' : '' }}>
                </div>
            </td>
            <td class="text-nowrap">
                <a href="{{ route('promo-codes.show', $promo) }}" class="btn btn-sm btn-outline-primary">Usages</a>
                <a href="{{ route('promo-codes.edit', $promo) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('promo-codes.destroy', $promo) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete this promo code?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8">No promo codes found.</td>
        </tr>
    @endforelse
    </tbody>
</table>
</div>
</div>

<div class="mt-3">
    {{ $promoCodes->links('pagination::bootstrap-5') }}
</div>
@endsection
