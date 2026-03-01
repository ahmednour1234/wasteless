@extends('layouts/layoutMaster')

@section('title', 'Reviews Details')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}" />
@endsection

@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('page-script')
  <script src="{{asset('assets/js/app-ecommerce-dashboard.js')}}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card mb-3">
    <div class="card-body">
      <h4 class="mb-3">{{ $review->bundle->name ?? 'Bundle deleted' }}</h4>
      <p><strong>Customer :</strong> {{ $review->customer->name ?? 'N/A' }}</p>
      <p><strong>Rating   :</strong> {{ $review->rating }} / 5</p>
      <p><strong>Comment  :</strong><br>{{ $review->comment ?? '—' }}</p>
      <p><strong>Status   :</strong>
        <span class="badge {{ $review->active ? 'bg-success' : 'bg-secondary' }}">
          {{ $review->active ? 'Active' : 'Inactive' }}
        </span>
      </p>

      {{-- Toggle button --}}
      <form action="{{ route('reviews.toggle', $review) }}"
            method="POST">
        @csrf @method('PATCH')
        <button class="btn {{ $review->active ? 'btn-outline-danger' : 'btn-outline-success' }}">
          {{ $review->active ? 'Deactivate' : 'Activate' }}
        </button>
      </form>
    </div>
  </div>

  <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
</div>
@endsection
