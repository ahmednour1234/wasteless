@extends('layouts/layoutMaster')

@section('title', 'Company Details')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-advance.css') }}">
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-md-8">
      <div class="card shadow-sm rounded-lg">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-building me-2 text-white"></i> Company Details</h5>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item">
              <strong>ID:</strong> {{ $company->id }}
            </li>
            <li class="list-group-item">
              <strong>Name:</strong> {{ $company->name }}
            </li>
            <li class="list-group-item">
              <strong>Email:</strong> {{ $company->email }}
            </li>
            <li class="list-group-item">
              <strong>Phone:</strong> {{ $company->phone }}
            </li>
            <li class="list-group-item">
              <strong>Active:</strong>
              @if($company->active)
                <span class="badge bg-success">Yes</span>
              @else
                <span class="badge bg-danger">No</span>
              @endif
            </li>
            <li class="list-group-item">
              <strong>Created At:</strong> {{ $company->created_at->format('Y-m-d H:i') }}
            </li>
          </ul>
        </div>
        <div class="card-footer text-center">
          <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i> Back to List
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
