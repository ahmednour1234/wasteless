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
            .then(data => {
                input.checked = !!data.status;
                const badge = input.closest('td').querySelector('.status-badge');
                badge.textContent = data.status ? 'Active' : 'Inactive';
                badge.className = 'badge status-badge ' + (data.status ? 'bg-label-success' : 'bg-label-secondary');
            })
            .catch(() => {
                input.checked = !input.checked;
                alert('Failed to update status');
            });
        });
    });
});
</script>
@endsection

{{-- content --}}
@section('content')

{{-- top bar --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Categories</h4>
        <p class="text-muted mb-0">Manage the categories customers browse in the app.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('category.create') }}">
        <i class="ti ti-plus me-1"></i> Add New Category
    </a>
</div>

{{-- stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="avatar avatar-md"><span class="avatar-initial rounded bg-label-primary"><i class="ti ti-category ti-md"></i></span></span>
                <div>
                    <small class="text-muted d-block">Total</small>
                    <h5 class="mb-0">{{ $totalCount }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="avatar avatar-md"><span class="avatar-initial rounded bg-label-success"><i class="ti ti-circle-check ti-md"></i></span></span>
                <div>
                    <small class="text-muted d-block">Active</small>
                    <h5 class="mb-0">{{ $activeCount }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="avatar avatar-md"><span class="avatar-initial rounded bg-label-secondary"><i class="ti ti-circle-x ti-md"></i></span></span>
                <div>
                    <small class="text-muted d-block">Inactive</small>
                    <h5 class="mb-0">{{ $totalCount - $activeCount }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    {{-- filter form --}}
    <div class="card-header border-bottom">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="name">Search</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="name" id="name" value="{{ request('name') }}"
                           class="form-control" placeholder="Search by name">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="active">Status</label>
                <select name="active" id="active" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
                <a href="{{ route('category.index') }}" class="btn btn-label-secondary">Reset</a>
            </div>
        </form>
    </div>

    {{-- table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 70px">#</th>
                    <th>Category</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($categories as $cat)
                <tr>
                    <td class="text-muted">{{ $categories->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-md">
                                @if ($cat->image)
                                    <img src="{{ \Illuminate\Support\Str::startsWith($cat->image, ['http://', 'https://']) ? $cat->image : asset($cat->image) }}"
                                         alt="{{ $cat->name }}" class="rounded object-fit-cover"
                                         onerror="this.replaceWith(Object.assign(document.createElement('span'), {className: 'avatar-initial rounded bg-label-primary', textContent: '{{ mb_strtoupper(mb_substr($cat->name, 0, 1)) }}'}))">
                                @else
                                    <span class="avatar-initial rounded bg-label-primary">{{ mb_strtoupper(mb_substr($cat->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="fw-medium">{{ $cat->name }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex align-items-center gap-2">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input toggle-status" type="checkbox"
                                       data-url="{{ route('category.toggle', $cat) }}"
                                       {{ $cat->is_active ? 'checked' : '' }}>
                            </div>
                            <span class="badge status-badge {{ $cat->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                {{ $cat->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('category.edit', $cat) }}" class="btn btn-sm btn-label-primary">
                            <i class="ti ti-edit me-1"></i> Edit
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <i class="ti ti-category-off ti-lg text-muted d-block mb-2"></i>
                        <span class="text-muted">No categories found.</span>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- pagination --}}
    @if ($categories->hasPages() || $categories->total())
    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">
            Showing {{ $categories->firstItem() ?? 0 }}–{{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }}
        </small>
        {{ $categories->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
