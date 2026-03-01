@extends('layouts/layoutMaster')

@section('title', __('Bundle Details'))

{{-- vendor css / js (unchanged) --}}
@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
@endsection
@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('content')

  {{-- back button --}}
  <a href="{{ route('bundels.index') }}" class="btn btn-outline-primary mb-4">
    <i class="ti ti-arrow-left"></i> {{ __('Back to Bundles') }}
  </a>

  {{-- nav tabs --}}
  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bundle-tab"
              role="tab">{{ __('Details') }}</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews-tab"
              role="tab">{{ __('Reviews') }}</button>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ============ Details tab ============ --}}
    <div class="tab-pane fade show active" id="bundle-tab" role="tabpanel">

      <div class="card shadow-sm position-relative overflow-hidden">

        @if($bundle->price_after_discount && $bundle->price_after_discount < $bundle->price)
          <span class="badge bg-label-danger position-absolute top-0 start-0 m-3">
            {{ __('Discount') }}
          </span>
        @endif

        <div class="row g-0">
          {{-- image --}}
          <div class="col-lg-4 col-md-5">
            <img src="{{ $bundle->image ? asset($bundle->image) :
                        asset('assets/img/placeholder/placeholder.jpg') }}"
                 class="card-img h-100 object-fit-cover"
                 alt="{{ $bundle->name }}">
          </div>

          {{-- data --}}
          <div class="col-lg-8 col-md-7">
            <div class="card-body d-flex flex-column h-100">

              <h4 class="card-title mb-3">
                {{ $bundle->name }}
                <span class="badge {{ $bundle->active ? 'bg-label-success' : 'bg-label-secondary' }}">
                  {{ $bundle->active ? __('Active') : __('Inactive') }}
                </span>
              </h4>

              <p class="text-muted mb-4">{{ $bundle->description }}</p>

              <div class="row mb-4">
                <div class="col-sm-6">
                  <Strong class="text-uppercase">{{ __('Store') }}</Strong>
                  <p class="fw-medium mb-0">{{ $bundle->company?->name ?? '-' }}</p>
                </div>
                <div class="col-sm-6">
                  <Strong class="text-uppercase">{{ __('Branch') }}</Strong>
                  <p class="fw-medium mb-0">{{ $bundle->branch?->name ?? '-' }}</p>
                </div>
                <div class="col-sm-6 mt-3">
                  <Strong class="text-uppercase">{{ __('Collection Opening Date') }}</Strong>
                  <p class="mb-0">{{ $bundle->opening_time }}</p>
                </div>
                <div class="col-sm-6 mt-3">
                  <Strong class="text-uppercase">{{ __('Collection End Date') }}</Strong>
                  <p class="mb-0">{{ $bundle->ended_time }}</p>
                </div>
                <div class="col-sm-6 mt-3">
                  <Strong class="text-uppercase">{{ __('Quantity Available') }}</Strong>
                  <p class="mb-0">{{ $bundle->stock }}</p>
                </div>
              </div>

              {{-- price --}}
                              <div class="col-sm-6 mt-3">
                                                  <Strong class="text-uppercase">{{ __('Price') }}</Strong>
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
                  <Strong >{{ config('app.currency', 'USD') }}</Strong>
                </h5>
              </div>

            </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ============ Reviews tab ============ --}}
    <div class="tab-pane fade" id="reviews-tab" role="tabpanel">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Rating') }}</th>
                <th>{{ __('Comment') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Date') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($reviews as $i => $r)
                <tr>
                  <td>{{ $i + $reviews->firstItem() }}</td>
                  <td>{{ $r->customer->name ?? '—' }}</td>
                  <td>{{ $r->rating }}/5</td>
                  <td>{{ Str::limit($r->comment, 60) }}</td>
                  <td>
                    <span class="badge {{ $r->active ? 'bg-success' : 'bg-secondary' }}">
                      {{ $r->active ? __('Active') : __('Inactive') }}
                    </span>
                  </td>
                  <td>{{ $r->created_at->format('d M Y') }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center p-4">{{ __('No reviews') }}</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if($reviews instanceof \Illuminate\Pagination\AbstractPaginator)
          <div class="card-footer">{{ $reviews->links() }}</div>
        @endif
      </div>
    </div>

  </div> {{-- /tab-content --}}

@endsection
