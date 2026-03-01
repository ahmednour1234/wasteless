@extends('layouts/layoutMaster')

@section('title', 'Customer')

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
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- === Filter card === --}}
  <div class="card mb-4">
    <div class="card-body">
      <form class="row g-2" method="GET" action="{{ route('customers.index') }}">
        <div class="col-md-4">
          <input type="text"
                 name="q"
                 class="form-control"
                 placeholder="Search by name or phone"
                 value="{{ request('q') }}">
        </div>
        <div class="col-auto">
          <button class="btn btn-primary">Search</button>
        </div>
        <div class="col-auto">
          <a href="{{ route('customers.index') }}"
             class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  {{-- === Data table === --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Image</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($customers as $k => $c)
            <tr>
              <td>{{ $k + $customers->firstItem() }}</td>
              <td>
                <img src="{{ $c->img ? asset($c->img) : asset('no-avatar.png') }}"
                     alt="avatar"
                     width="40"
                     class="rounded-circle">
              </td>
              <td>{{ $c->name }}</td>
              <td>{{ $c->email }}</td>
              <td>{{ $c->phone }}</td>
              <td class="text-center">
                <a href="{{ route('customers.show', $c) }}"
                   class="btn btn-sm btn-outline-primary">
                  Show
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center p-4">No records found</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer">
      {{ $customers->links() }}
    </div>
  </div>

</div>
@endsection
