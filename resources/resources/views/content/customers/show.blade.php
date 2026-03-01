@extends('layouts/layoutMaster')

@section('title', 'Customer Details')

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

  <div class="card">
    <div class="card-body d-flex gap-4">
      <img src="{{ $customer->img ? asset($customer->img) : asset('no-avatar.png') }}"
           alt="avatar"
           width="120"
           class="rounded-circle">

      <div class="flex-fill">
        <h4 class="mb-2">{{ $customer->name }}</h4>
        <p class="mb-1"><strong>Email :</strong> {{ $customer->email }}</p>
        <p class="mb-0"><strong>Phone :</strong> {{ $customer->phone }}</p>
      </div>
    </div>
  </div>

  <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Back</a>

</div>
@endsection
