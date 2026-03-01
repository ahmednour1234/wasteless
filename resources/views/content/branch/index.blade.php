@extends('layouts/layoutMaster')

@section('title', 'Branches')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/swiper/swiper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/cards-advance.css')}}">
<style>
.pagination .page-item.active .page-link {
  background-color: #696cff;
  border-color: #696cff;
}
.pagination .page-link {
  color: #696cff;
  border-radius: 6px;
  margin: 0 2px;
}
</style>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
<script src="{{asset('assets/vendor/libs/swiper/swiper.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Branches</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('branches') }}" class="mb-4 row g-2">
      <div class="col-md-3">
        <label for="company_id" class="form-label">Store</label>
        <select name="company_id" id="company_id" class="form-select">
          <option value="">All Stores</option>
          @foreach($companies as $company)
            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
              {{ $company->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label for="name" class="form-label">Branch Name</label>
        <input type="text" name="name" id="name" value="{{ request('name') }}" class="form-control" placeholder="Search by name">
      </div>
      <div class="col-md-3">
        <label for="phone" class="form-label">Branch Phone</label>
        <input type="text" name="phone" id="phone" value="{{ request('phone') }}" class="form-control" placeholder="Search by phone">
      </div>
      <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-primary me-2">Filter</button>
        <a href="{{ route('branches') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>Phone</th>
            <th>Store</th>
            <th>Status</th>
            <th class="text-center">Map</th>
          </tr>
        </thead>
        <tbody>
          @forelse($branches as $branch)
            <tr>
              <td>{{ $branch->id }}</td>
              <td>{{ $branch->name }}</td>
              <td>{{ $branch->address }}</td>
              <td>{{ $branch->phone }}</td>
              <td>{{ $branch->company->name }}</td>
              <td>
                @if($branch->active)
                  <span class="badge bg-success">Active</span>
                @else
                  <span class="badge bg-danger">Inactive</span>
                @endif
              </td>
              <td class="text-center">
                <a href="https://www.google.com/maps?q={{ $branch->lat }},{{ $branch->lng }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-geo-alt-fill me-1"></i> View
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center">No branches found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
      {{ $branches->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>
@endsection