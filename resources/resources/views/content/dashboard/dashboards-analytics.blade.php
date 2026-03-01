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
            <span>@lang("Bundels")</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">{{$Bundels}}</h3>
            </div>
            <p class="mb-0">@lang("Total Bundels")</p>
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
                <h6 class="mb-0">@lang("Branches  Today")</h6>
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
                <h6 class="mb-0">@lang("Bundels")</h6>
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
          <h5 class="mb-0">@lang("Bundels")</h5>
          <small class="text-muted">@lang("Today")</small>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-sm-4 col-md-12 col-lg-4">
            <div class="mt-lg-4 mt-lg-2 mb-lg-4 mb-2 pt-1">
              <h1 class="mb-0">{{ number_format($orderapprovecount / 1024, 2) }}</h1>
              <p class="mb-0">@lang("Bundels Pdfs")</p>
            </div>
            <ul class="p-0 m-0">
              <li class="d-flex gap-3 align-items-center mb-lg-3 pt-2 pb-1">
                <div class="badge rounded bg-label-primary p-1">
                  <i class="ti ti-ticket ti-sm"></i>
                </div>
                <div>
                  <h6 class="mb-0 text-nowrap">@lang("Size Used")</h6>
                  <small class="text-muted">{{ number_format($orderapprovecount / 1024, 2) }} KB</small>
                </div>
              </li>
              <li class="d-flex gap-3 align-items-center mb-lg-3 pb-1">
                <div class="badge rounded bg-label-info p-1">
                  <i class="ti ti-circle-check ti-sm"></i>
                </div>
                <div>
                  <h6 class="mb-0 text-nowrap">@lang("Sized Free")</h6>
                 @php
    $totalSize = 50 * 1024 * 1024 * 1024; // 50GB in bytes
    $remainingSize = $totalSize - $orderapprovecount; // remaining size in bytes
    $remainingSizeInMB = $remainingSize / 1024 / 1024; // Convert to MB
    $remainingSizeInGB = $remainingSize / 1024 / 1024 / 1024; // Convert to GB
@endphp


              <small class="text-muted">
                  @if ($remainingSizeInGB >= 1)
                      {{ number_format($remainingSizeInGB, 2) }} KB
                  @else
                      {{ number_format($remainingSizeInMB, 2) }} KB
                  @endif
              </small>
                              </div>
              </li>
            </ul>
          </div>
          <div class="col-12 col-sm-8 col-md-12 col-lg-8">
            <div id="supportTracker"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Support Tracker -->
  {{-- <div style="padding-bottom: 30px;" class="card">
    <h5 class="card-header">@lang("Fastest Sellers")</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>@lang("Name")</th>
            <th>@lang("Branch")</th>
            <th>@lang("Time")</th>
            <th>@lang("Number of Car")</th>
            <th>@lang("Action")</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <tr>
            <td><span class="fw-medium">Ahmed Karem</span></td>
            <td>Naser City</td>
            <td>02:30</td>
            <td>30</td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="ti ti-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="javascript:void(0);">
                    <i class="ti ti-burst-hover"></i> @lang("Enter Survey")
                  </a>
                </div>
              </div>
            </td>
          </tr>
          <!-- More Rows as needed -->
        </tbody>
      </table>
    </div>
  </div> --}}
</div>


@endsection
