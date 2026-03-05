@extends('layouts.layoutMaster')

@section('title', 'Application Settings')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}">
@endsection

@section('content')
  <!-- Settings Form -->
  <div class="card mb-4">
    <h5 class="card-header">@lang('Update Application Data')</h5>
   <form class="card-body" method="POST" action="{{ route('dashboard-setting-store') }}" enctype="multipart/form-data">
  @csrf
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">@lang('Application Name')</label>
      <input type="text"
             class="form-control"
             name="name"
             placeholder="@lang('Application Name')"
             value="{{ old('name', $settings->name ?? '') }}" />
    </div>

    <div class="col-md-6">
      <label class="form-label">@lang('Phone')</label>
      <input type="number"
             class="form-control"
             name="phone"
             placeholder="@lang('Phone')"
             value="{{ old('phone', $settings->phone ?? '') }}" />
    </div>

    <div class="col-md-6">
      <label class="form-label">@lang('Address')</label>
      <input type="text"
             class="form-control"
             name="address"
             placeholder="@lang('Address')"
             value="{{ old('address', $settings->address ?? '') }}" />
    </div>

    <div class="col-md-6">
      <label class="form-label">@lang('Logo Image')</label>
      <input type="file"
             class="form-control"
             name="img"
             accept="image/*" />
      @if(isset($settings->img))
        <img src="{{ asset($settings->img) }}" alt="Logo Image" class="mt-2" style="width: 100px; height: auto;">
      @endif
    </div>

    <div class="col-md-6">
      <label class="form-label">@lang('Commission Percentage') (%)</label>
      <input type="number"
             class="form-control"
             name="commission_percentage"
             step="0.01"
             min="0"
             max="100"
             placeholder="@lang('Commission Percentage')"
             value="{{ old('commission_percentage', $settings->commission_percentage ?? 0) }}" />
      <small class="text-muted">@lang('The percentage that will be taken from each successful order payment')</small>
    </div>
  </div>

  <div class="pt-4">
    <button type="submit" class="btn btn-primary me-sm-3 me-1">@lang('Submit')</button>
  </div>
</form>

  </div>
@endsection

@section('vendor-script')
  <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/@form-validation/umd/index.min.js') }}"></script>
@endsection
