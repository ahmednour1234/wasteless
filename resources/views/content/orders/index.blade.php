@extends('layouts/layoutMaster')

@section('title', 'Orders Analytics')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-advance.css') }}">
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
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    $('.select2').select2({
      width: '100%',
      placeholder: '-- Select --'
    });
  });
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Orders List</h5>
    <form method="GET" action="{{ route('orders.index') }}" class="w-100">
      <div class="row g-2">
        <div class="col-md-2">
          <select name="bundle_id" class="form-select select2">
            <option value="">All Bundles</option>
            @foreach($bundles as $bundle)
              <option value="{{ $bundle->id }}" {{ request('bundle_id') == $bundle->id ? 'selected' : '' }}>{{ $bundle->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <select name="company_id" class="form-select select2">
            <option value="">All Companies</option>
            @foreach($companies as $company)
              <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <select name="branch_id" class="form-select select2">
            <option value="">All Branches</option>
            @foreach($branches as $branch)
              <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <select name="category_id" class="form-select select2">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <input type="number" name="quantity" class="form-control" placeholder="Quantity" value="{{ request('quantity') }}">
        </div>
        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card-body table-responsive">
    <table class="table table-hover table-striped">
      <thead class="table-light">
        <tr class="text-center">
          <th>#</th>
          <th>Customer</th>
          <th>Status</th>
          <th>Subtotal</th>
          <th>Discount</th>
          <th>Total</th>
          <th># Bundles</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
        <tr class="text-center">
          <td>{{ $order->id }}</td>
          <td>{{ $order->name }}</td>
          <td><span class="badge bg-info text-capitalize">{{ $order->status }}</span></td>
          <td>{{ number_format($order->sub_total, 2) }} Lpb</td>
          <td class="text-danger">-{{ number_format($order->total_discount, 2) }} Lpb</td>
          <td class="text-success">
            {{ number_format($order->sub_total + $order->delivery - $order->total_discount, 2) }} Lpb
          </td>
          <td><span class="badge bg-secondary">{{ $order->details->count() }}</span></td>
          <td>
            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">View</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center">No orders found</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <div class="d-flex justify-content-center mt-4">
      {{ $orders->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>
@endsection
