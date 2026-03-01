@extends('layouts/layoutMaster')

@section('title', 'eCommerce Dashboard - Apps')

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
  <div class="row">
    <!-- View sales -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Sales Users")</h5>
                        <p class="mb-2">@lang("Data of sales users")</p>
                        <h4 class="text-primary mb-1">{{$salecount}}</h4>
                        <a href="{{ route('exportSellers', ['type' => 'sale']) }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Sales')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View sales -->

    <!-- View sellers -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Sellers Users")</h5>
                        <p class="mb-2">@lang("Data of sellers users")</p>
                        <h4 class="text-primary mb-1">{{$sellercount}}</h4>
                        <a href="{{ route('exportSellers', ['type' => 'seller']) }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Sellers')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View sellers -->

    <!-- View team leaders -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Team Leaders")</h5>
                        <p class="mb-2">@lang("Data of team leader users")</p>
                        <h4 class="text-primary mb-1">{{$leadercount}}</h4>
                        <a href="{{ route('exportSellers', ['type' => 'leader']) }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Team Leaders')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View team leaders -->

    <!-- View managers -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Manager Users")</h5>
                        <p class="mb-2">@lang("Data of manager users")</p>
                        <h4 class="text-primary mb-1">{{$managercount}}</h4>
                        <a href="{{ route('exportSellers', ['type' => 'manager']) }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Managers')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View managers -->

    <!-- View dashboard users -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Dashboard Users")</h5>
                        <p class="mb-2">@lang("Data of dashboard users")</p>
                        <h4 class="text-primary mb-1">{{$usercount}}</h4>
                        <a href="{{ route('users.export') }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Dashboard Users')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View dashboard users -->

    <!-- View surveys -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Surveys Data")</h5>
                        <p class="mb-2">@lang("Data of surveys")</p>
                        <h4 class="text-primary mb-1">{{$surveycount}}</h4>
                        <a href="{{ route('survey.export', request()->query()) }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Surveys')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View surveys -->

    <!-- View sales requests -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Sales Requests")</h5>
                        <p class="mb-2">@lang("Data of sales requests")</p>
                        <h4 class="text-primary mb-1">{{$ordercount}}</h4>
                        <a href="{{ route('dashboard-salesrequest-export', ['type' => 'sale']) }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Sales Requests')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View sales requests -->

    <!-- View banks -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Banks Data")</h5>
                        <p class="mb-2">@lang("Data of banks")</p>
                        <h4 class="text-primary mb-1">{{$bankcount}}</h4>
                        <a href="{{ route('export_banks') }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Banks')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View banks -->

    <!-- View cars -->
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">@lang("Cars Data")</h5>
                        <p class="mb-2">@lang("Data of cars")</p>
                        <h4 class="text-primary mb-1">{{$carcount}}</h4>
                        <a href="{{ route('dashboard.export_cars') }}" class="btn btn-primary">@lang("Export Excel")</a>
                    </div>
                </div>
                <div class="col-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140" alt="@lang('View Cars')">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- View cars -->
</div>

@endsection
