@extends('layouts/layoutMaster')

@section('title', __('Bundle Details'))

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
    {{-- زر رجوع --}}
    <a href="{{ route('bundels.index') }}" class="btn btn-outline-primary mb-4">
      <i class="ti ti-arrow-left"></i> {{ __('Back to Bundles') }}
    </a>

    <div class="card shadow-sm position-relative overflow-hidden">
      {{-- خصم؟ --}}
      @if($bundle->price_after_discount && $bundle->price_after_discount < $bundle->price)
        <span class="badge bg-label-danger position-absolute top-0 start-0 m-3">
          {{ __('Discount') }}
        </span>
      @endif

      <div class="row g-0">
        {{-- الصورة --}}
        <div class="col-lg-4 col-md-5">
          <img
            src="{{ $bundle->image ? asset($bundle->image) : asset('assets/img/placeholder/placeholder.jpg') }}"
            class="card-img h-100 object-fit-cover"
            alt="{{ $bundle->name }}"
          >
        </div>

        {{-- التفاصيل --}}
        <div class="col-lg-8 col-md-7">
          <div class="card-body d-flex flex-column h-100">
            {{-- العنوان + الحالة --}}
            <h4 class="card-title mb-3">
              {{ $bundle->name }}
              @if($bundle->active)
                <span class="badge bg-label-success">{{ __('Active') }}</span>
              @else
                <span class="badge bg-label-secondary">{{ __('Inactive') }}</span>
              @endif
            </h4>

            {{-- الوصف --}}
            <p class="text-muted mb-4">{{ $bundle->description }}</p>

            {{-- بيانات أساسية --}}
            <div class="row mb-4">
              <div class="col-sm-6">
                <small class="text-uppercase text-muted">{{ __('Company') }}</small>
                <p class="fw-medium mb-0">{{ $bundle->company?->name ?? '-' }}</p>
              </div>
              <div class="col-sm-6">
                <small class="text-uppercase text-muted">{{ __('Branch') }}</small>
                <p class="fw-medium mb-0">{{ $bundle->branch?->name ?? '-' }}</p>
              </div>
              <div class="col-sm-6 mt-3">
                <small class="text-uppercase text-muted">{{ __('Opening Date') }}</small>
                <p class="mb-0">
                  {{ $bundle->opening_time }}
                </p>
              </div>
              <div class="col-sm-6 mt-3">
                <small class="text-uppercase text-muted">{{ __('End Date') }}</small>
                <p class="mb-0">
                  {{ $bundle->ended_time }}
                </p>
              </div>
              <div class="col-sm-6 mt-3">
                <small class="text-uppercase text-muted">{{ __('Quantity Avaliable') }}</small>
                <p class="mb-0">{{ $bundle->stock }}</p>
              </div>
            </div>

            {{-- السعر --}}
            <div class="mt-auto">
              <h5 class="mb-0">
                @if($bundle->price_after_discount && $bundle->price_after_discount < $bundle->price)
                  <span class="text-muted text-decoration-line-through me-2">
                    {{ number_format($bundle->price, 2) }}
                  </span>
                  <span class="text-primary">
                    {{ number_format($bundle->price_after_discount, 2) }}
                  </span>
                @else
                  <span class="text-primary">{{ number_format($bundle->price, 2) }}</span>
                @endif
                <small class="text-muted">{{ config('app.currency', 'USD') }}</small>
              </h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
