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
<div class="row g-4 mb-4">

  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>@lang("Stores")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">{{$storesCount}}</h3>
            </div>
            <p class="mb-0">@lang("Total Stores")</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="ti ti-user-plus ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>@lang("Branches")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">{{$Branches}}</h3>
            </div>
            <p class="mb-0">@lang("Total Branches")</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="ti ti-user-plus ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>@lang("Admins")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">{{$admincount}}</h3>
            </div>
            <p class="mb-0">@lang("Total Admins")</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="ti ti-user-check ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>@lang("Bundles")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">{{$Bundels}}</h3>
            </div>
            <p class="mb-0">@lang("Total Bundles")</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ti ti-user-exclamation ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>@lang("Total Revenue")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">${{ number_format($totalRevenue ?? 0, 2) }}</h3>
            </div>
            <p class="mb-0">@lang("Total Income")</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-info">
              <i class="ti ti-chart-line ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>@lang("Commission Percentage")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">{{ number_format($commissionPercentage ?? 0, 2) }}%</h3>
            </div>
            <p class="mb-0">@lang("Commission Rate")</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ti ti-percentage ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>@lang("Total Commission")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">${{ number_format($totalCommission ?? 0, 2) }}</h3>
            </div>
            <p class="mb-0">@lang("From Transactions")</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ti ti-currency-dollar ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span>@lang("Orders Commission")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">${{ number_format($totalOrdersCommission ?? 0, 2) }}</h3>
            </div>
            <p class="mb-0">@lang("From Orders")</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="ti ti-receipt ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <!-- Earning Reports -->
  <div class="col-lg-6 mb-4">
    <div class="card h-100">
      <div class="card-header pb-0 d-flex justify-content-between mb-lg-n4">
        <div class="card-title mb-0">
          <h5 class="mb-0">@lang("Branches")</h5>
          <small class="text-muted">@lang("Branches")</small>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-4 d-flex flex-column align-self-end">
            <div class="d-flex gap-2 align-items-center mb-2 pb-1 flex-wrap">
              <h1 class="mb-0">{{$Branches}}</h1>
            </div>
            <small>@lang("Branches Branches")</small>
          </div>
          <div class="col-12 col-md-8">
            <div id="weeklyEarningReports"></div>
          </div>
        </div>
        <div class="border rounded p-3 mt-4">
          <div class="row gap-4 gap-sm-0">
            <div class="col-12 col-sm-4">
              <div class="d-flex gap-2 align-items-center">
                <div class="badge rounded bg-label-primary p-1">
                  <i class="ti ti-chart-pie-2 ti-sm"></i>
                </div>
                <h6 class="mb-0">@lang("Stores Today")</h6>
              </div>
              <h4 class="my-2 pt-1">{{$Branches}}</h4>
              <div class="progress w-75" style="height:4px">
                <div class="progress-bar" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <div class="col-12 col-sm-4">
              <div class="d-flex gap-2 align-items-center">
                <div class="badge rounded bg-label-info p-1">
                  <i class="ti ti-chart-pie-2 ti-sm"></i>
                </div>
                <h6 class="mb-0">@lang("Admins Added Today")</h6>
              </div>
              <h4 class="my-2 pt-1">{{$admincount}}</h4>
              <div class="progress w-75" style="height:4px">
                <div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <div class="col-12 col-sm-4">
              <div class="d-flex gap-2 align-items-center">
                <div class="badge rounded bg-label-danger p-1">
                  <i class="ti ti-chart-pie-2 ti-sm"></i>
                </div>
                <h6 class="mb-0">@lang("Bundles")</h6>
              </div>
              <h4 class="my-2 pt-1">{{$Bundels}}</h4>
              <div class="progress w-75" style="height:4px">
                <div class="progress-bar bg-danger" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Earning Reports -->
  <!-- Support Tracker -->
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between pb-0">
        <div class="card-title mb-0">
          <h5 class="mb-0">@lang("Stores")</h5>
          <small class="text-muted">@lang("Today")</small>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-sm-4 col-md-12 col-lg-4">
            <div class="mt-lg-4 mt-lg-2 mb-lg-4 mb-2 pt-1">
              <p class="mb-0">@lang("Stores")</p>
              <h1>{{$companynotactives->count()}}</h1>
            </div>
        
          </div>
          <div class="col-12 col-sm-8 col-md-12 col-lg-8">
            <div id="supportTracker"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Support Tracker -->
</div>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
      
        <th>Status</th>
        <th>Joined At</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
<tbody>
  @forelse($companynotactives as $company)
    <tr>
      <td>{{ $company->id }}</td>
      <td>{{ $company->name }}</td>

      <td>
        @if($company->active)
          <span class="badge bg-success">Active</span>
        @else
          <span class="badge bg-danger">Inactive</span>
        @endif
      </td>
      <td>{{ $company->created_at->format('Y-m-d H:i') }}</td>
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
      <td colspan="7" class="text-center">No companies found.</td>
    </tr>
  @endforelse
</tbody>


  </table>


@endsection
