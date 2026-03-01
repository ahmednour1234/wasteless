@extends('layouts/layoutMaster')

@section('title', 'Bundles List')

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
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-active').forEach(function (checkbox) {
      checkbox.addEventListener('change', function () {
        const id = this.dataset.id;
        fetch(`/bundels/${id}/toggle`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        }).then(res => res.json())
          .then(data => {
            // يمكن إضافة Toast هنا عند الحاجة
          }).catch(() => {
            // في حال الخطأ أعد الحالة إلى ما كانت عليه
            this.checked = !this.checked;
          });
      });
    });
  });
</script>
@endsection

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">{{ __('Bundles List') }}</h5>
    </div>

    <!-- ---------- Filters ---------- -->
    <div class="card-body pb-0">
      <form class="row g-3" method="GET" action="{{ route('bundels.index') }}">
        <!-- Company -->
        <div class="col-md-3">
          <label class="form-label">{{ __('Company') }}</label>
          <select name="company_id" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach($companies ?? [] as $company)
              <option value="{{ $company->id }}" @selected(request('company_id') == $company->id)>
                {{ $company->name }}
              </option>
            @endforeach
          </select>
        </div>

        <!-- Name -->
        <div class="col-md-3">
          <label class="form-label">{{ __('Name') }}</label>
          <input type="text" name="name" class="form-control" value="{{ request('name') }}">
        </div>

        <!-- Price Range -->
        <div class="col-md-3">
          <label class="form-label">{{ __('Price From') }}</label>
          <input type="number" min="0" step="0.01" name="price_from" class="form-control" value="{{ request('price_from') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('Price To') }}</label>
          <input type="number" min="0" step="0.01" name="price_to" class="form-control" value="{{ request('price_to') }}">
        </div>

        <!-- Discount? -->
        <div class="col-md-3">
          <label class="form-label">{{ __('Has Discount?') }}</label>
          <select name="has_discount" class="form-select">
            <option value="">{{ __('All') }}</option>
            <option value="1" @selected(request('has_discount') === '1')>{{ __('Yes') }}</option>
            <option value="0" @selected(request('has_discount') === '0')>{{ __('No') }}</option>
          </select>
        </div>

        <!-- Active -->
        <div class="col-md-3">
          <label class="form-label">{{ __('Active') }}</label>
          <select name="active" class="form-select">
            <option value="">{{ __('All') }}</option>
            <option value="1" @selected(request('active') === '1')>{{ __('Active') }}</option>
            <option value="0" @selected(request('active') === '0')>{{ __('Inactive') }}</option>
          </select>
        </div>

        <!-- Opening date range -->
        <div class="col-md-3">
          <label class="form-label">{{ __('Opening From') }}</label>
          <input type="date" name="opening_from" class="form-control" value="{{ request('opening_from') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('Opening To') }}</label>
          <input type="date" name="opening_to" class="form-control" value="{{ request('opening_to') }}">
        </div>

        <!-- Ended date range -->
        <div class="col-md-3">
          <label class="form-label">{{ __('Ended From') }}</label>
          <input type="date" name="ended_from" class="form-control" value="{{ request('ended_from') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('Ended To') }}</label>
          <input type="date" name="ended_to" class="form-control" value="{{ request('ended_to') }}">
        </div>

        <!-- Created at range -->
        <div class="col-md-3">
          <label class="form-label">{{ __('Created From') }}</label>
          <input type="date" name="created_from" class="form-control" value="{{ request('created_from') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('Created To') }}</label>
          <input type="date" name="created_to" class="form-control" value="{{ request('created_to') }}">
        </div>

        <!-- Buttons -->
        <div class="col-12 d-flex justify-content-end gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
          <a href="{{ route('bundels.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
           <a href="{{ route('bundels.create') }}" class="btn btn-outline-secondary">{{ __('Create') }}</a>
        </div>
      </form>
    </div>

    <!-- ---------- Table ---------- -->
    <div class="table-responsive pt-4">
      <table class="table align-middle text-center mb-0">
        <thead >
          <tr>
            <th>#</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Company') }}</th>
            <th>{{ __('Opening Date') }}</th>
            <th>{{ __('Ended Date') }}</th>
            <th>{{ __('Price') }}</th>
            <th>{{ __('Discount?') }}</th>
            <th>{{ __('Active') }}</th>
                        <th>{{ __('Show') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($bundles as $index => $bundle)
          <tr>
            <td>{{ $index + $bundles->firstItem() }}</td>
            <td>{{ $bundle->name }}</td>
            <td>{{ $bundle->company->name ?? '-' }}</td>
            <td>{{ $bundle->opening_time }}</td>
            <td>{{ $bundle->ended_time }}</td>
            <td>{{ number_format($bundle->price, 2) }}</td>
            <td>
              @if(isset($bundle->price_after_discount) && $bundle->price_after_discount < $bundle->price)
                <span class="badge bg-success">
                  {{ __('Yes') }} ({{ number_format($bundle->price_after_discount, 2) }})
                </span>
              @else
                <span class="badge bg-secondary">{{ __('No') }}</span>
              @endif
            </td>
            <td>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input toggle-active" type="checkbox" data-id="{{ $bundle->id }}" {{ $bundle->active ? 'checked' : '' }}>
              </div>
            </td>
               <td class="text-end">
            <a
              href="{{ route('bundels.show', $bundle) }}"
              class="btn btn-sm btn-outline-info"
            >Show</a>
          </td>
          </tr>
          @empty
            <tr>
              <td colspan="8" class="text-muted">{{ __('No bundles found') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer">
      {{ $bundles->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection

