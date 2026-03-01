@extends('layouts/layoutMaster')

@section('title', 'Reviews')

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

  {{-- Search --}}
  <div class="card mb-4">
    <div class="card-body">
      <form class="row g-2" method="GET">
        <div class="col-md-4">
          <input type="text" name="q" class="form-control" placeholder="Search bundle or customer"
                 value="{{ request('q') }}">
        </div>
        <div class="col-auto">
          <button class="btn btn-primary">Search</button>
        </div>
        <div class="col-auto">
          <a href="{{ route('reviews.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Bundle</th>
            <th>Store</th>
            <th>Customer</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Status</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reviews as $k => $r)
            <tr>
              <td>{{ $k + $reviews->firstItem() }}</td>
              <td>{{ $r->bundle->name ?? '-' }}</td>
                            <td>{{ $r->bundle->company->name ?? '-' }}</td>
              <td>{{ $r->customer->name ?? '-' }}</td>
              <td>{{ $r->rating }}/5</td>
              <td>{{ Str::limit($r->comment, 40) }}</td>
              <td>
                <span class="badge {{ $r->active ? 'bg-success' : 'bg-secondary' }}">
                  {{ $r->active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="text-center">
                <a href="{{ route('reviews.show', $r) }}"
                class="btn btn-sm btn-outline-primary">Show</a>

                {{-- toggle button --}}
                <form action="{{ route('reviews.toggle', $r) }}"
                      method="POST" class="d-inline">
                  @csrf @method('PATCH')
                  <button class="btn btn-sm {{ $r->active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                    {{ $r->active ? 'Deactivate' : 'Activate' }}
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center p-4">No reviews found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $reviews->links() }}</div>
  </div>
</div>
@endsection
