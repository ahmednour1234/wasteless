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
  <div class="col-12 col-md-12">

    {{-- Nav Tabs --}}
    <ul class="nav nav-tabs mb-4" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#details-tab" role="tab">
          Details
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#branches-tab" role="tab">
          Branches
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reset-password-tab" role="tab">
          Reset Password
        </button>
      </li>
    </ul>

    <div class="tab-content">
      {{-- Details Tab --}}
      <div class="tab-pane fade show active" id="details-tab" role="tabpanel">
        <div class="card shadow-sm rounded-lg">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-building me-2 text-white"></i> Company Details</h5>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item"><strong>ID:</strong> {{ $company->id }}</li>
              <li class="list-group-item"><strong>Name:</strong> {{ $company->name }}</li>
              <li class="list-group-item"><strong>Email:</strong> {{ $company->email }}</li>
              <li class="list-group-item"><strong>Phone:</strong> {{ $company->phone }}</li>
              <li class="list-group-item"><strong>Category:</strong> {{ $company->category->name ?? '' }}</li>
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

      {{-- Branches Tab --}}
      <div class="tab-pane fade" id="branches-tab" role="tabpanel">
        <div class="card shadow-sm rounded-lg">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-diagram-3 me-2 text-white"></i> Branches</h5>
          </div>
          <div class="card-body">
            @if($branches->isEmpty())
              <p>No branches found for this company.</p>
            @else
              <div class="table-responsive">
                <table class="table table-striped mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Address</th>
                      <th>Phone</th>
                      <th>Map</th>
                      <th>Active</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($branches as $index => $branch)
                      <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $branch->name }}</td>
                        <td>{{ $branch->address ?? '-' }}</td>
                        <td>{{ $branch->phone ?? '-' }}</td>
                        <td>
                          @if($branch->lat && $branch->lng)
                            <a href="https://www.google.com/maps?q={{ $branch->lat }},{{ $branch->lng }}"
                               target="_blank" class="btn btn-sm btn-outline-primary">
                              Show Map
                            </a>
                          @else
                            <span class="text-muted">No location</span>
                          @endif
                        </td>
                        <td>
                          @if($branch->active)
                            <span class="badge bg-success">Yes</span>
                          @else
                            <span class="badge bg-danger">No</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
          <div class="card-footer text-center">
            <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left-circle me-1"></i> Back to List
            </a>
          </div>
        </div>
      </div>

      {{-- Reset Password Tab --}}
      <div class="tab-pane fade" id="reset-password-tab" role="tabpanel">
        <div class="card shadow-sm rounded-lg">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-key me-2 text-white"></i> Reset Store Password</h5>
          </div>
          <div class="card-body">
            <form method="POST" action="{{ route('companies.updatePassword', $company->id) }}">
              @csrf
              @method('PUT')

              <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required>
                @error('password')
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>

              <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
              </div>

              <div class="text-center">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-circle me-1"></i> Update Password
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
