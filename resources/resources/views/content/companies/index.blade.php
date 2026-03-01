@extends('layouts/layoutMaster')

@section('title', 'Analytics')

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
  <h1 class="mb-4">Companies</h1>

  <form method="GET" action="{{ route('companies.index') }}" class="row g-2 mb-4">
    <div class="col-auto">
      <input
        type="text"
        name="name"
        value="{{ request('name') }}"
        class="form-control"
        placeholder="Filter by name"
      >
    </div>
    <div class="col-auto">
      <input
        type="text"
        name="phone"
        value="{{ request('phone') }}"
        class="form-control"
        placeholder="Filter by phone"
      >
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="{{ route('companies.index') }}" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Status</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($companies as $company)
        <tr>
          <td>{{ $company->id }}</td>
          <td>{{ $company->name }}</td>
          <td>{{ $company->email }}</td>
          <td>{{ $company->phone }}</td>
          <td>
            @if($company->active)
              <span class="badge bg-success">Active</span>
            @else
              <span class="badge bg-danger">Inactive</span>
            @endif
          </td>
          <td class="text-end">
            <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-outline-info">Show</a>
            <form action="{{ route('companies.toggle', $company) }}" method="POST" style="display:inline-block">
              @csrf
              @method('PATCH')
              <button type="submit" class="btn btn-sm {{ $company->active ? 'btn-warning' : 'btn-success' }}">
                {{ $company->active ? 'Deactivate' : 'Activate' }}
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center">No companies found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="d-flex justify-content-center mt-4">
    {{ $companies->links('pagination::bootstrap-5') }}
  </div>
@endsection