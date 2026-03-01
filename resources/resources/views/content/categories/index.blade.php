@extends('layouts/layoutMaster')

@section('title', 'Categories')

{{-- vendor CSS --}}
@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}">
@endsection

{{-- extra page CSS --}}
@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-advance.css') }}">
@endsection

{{-- vendor JS --}}
@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

{{-- page-level JS --}}
@section('page-script')
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-status').forEach(el => {
        el.addEventListener('change', e => {
            const id = e.target.dataset.id;
            fetch(`/category/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => console.log('New status:', data.status));
        });
    });
});
</script>
@endsection

{{-- content --}}
@section('content')

{{-- top bar --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Categories</h4>
    <a class="btn btn-primary" href="{{ route('category.create') }}">Add New Category</a>
</div>

{{-- filter form --}}
<form method="GET" class="card p-3 mb-4">
    <div class="row g-3 align-items-end">

        {{-- name filter --}}
        <div class="col-md-4">
            <label class="form-label" for="name">Name contains</label>
            <input type="text"
                   name="name"
                   id="name"
                   value="{{ request('name') }}"
                   class="form-control"
                   placeholder="Search by name">
        </div>

        {{-- active filter --}}
        <div class="col-md-3">
            <label class="form-label" for="active">Status</label>
            <select name="active"
                    id="active"
                    class="form-select">
                <option value="" {{ request('active') === null ? 'selected' : '' }}>Any</option>
                <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        {{-- buttons --}}
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary w-100">Filter</button>
            <a href="{{ route('category.index') }}" class="btn btn-light w-100">Reset</a>
        </div>
    </div>
</form>

{{-- table --}}
<div class="table-responsive">
<table class="table  align-middle text-center">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Image</th>
            <th>Active?</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($categories as $cat)
        <tr>
            <td>{{ $loop->iteration + ($categories->currentPage()-1)*$categories->perPage() }}</td>
            <td>{{ $cat->name }}</td>
            <td>
                @if ($cat->image)
                    <img src="{{ asset($cat->image) }}" width="50" alt="image">
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input toggle-status"
                           type="checkbox"
                           data-id="{{ $cat->id }}"
                           {{ $cat->is_active ? 'checked' : '' }}>
                </div>
            </td>
            <td>
                <a href="{{ route('category.edit', $cat) }}" class="btn btn-sm btn-primary">Edit</a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5">No categories found.</td>
        </tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- pagination --}}
<div class="mt-3">
    {{ $categories->links() }}
</div>
@endsection
