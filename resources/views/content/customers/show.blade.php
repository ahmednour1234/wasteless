@extends('layouts/layoutMaster')

@section('title', 'Customer Details')

{{-- vendor css / js kept for DataTables if needed later --}}
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('content')

  {{-- Back button --}}
  <a href="{{ url()->previous() }}" class="btn btn-outline-primary mb-4">
    <i class="ti ti-arrow-left"></i> Back
  </a>

  {{-- Nav tabs --}}
  <ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-tab"
              role="tab">Profile</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews-tab"
              role="tab">Reviews</button>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ========== Profile tab ========== --}}
    <div class="tab-pane fade show active" id="profile-tab" role="tabpanel">
      <div class="card">
        <div class="card-body d-flex gap-4">
          <img src="{{ $customer->img ? asset($customer->img) : asset('no-avatar.png') }}"
               alt="avatar" width="120" class="rounded-circle">

          <div class="flex-fill">
            <h4 class="mb-2">{{ $customer->name }}</h4>
            <p class="mb-1"><strong>Email :</strong> {{ $customer->email }}</p>
            <p class="mb-0"><strong>Phone :</strong> {{ $customer->phone }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== Reviews tab ========== --}}
    <div class="tab-pane fade" id="reviews-tab" role="tabpanel">
      <div class="card">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Bundle</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($reviews as $i => $r)
                <tr>
                  <td>{{ $i + $reviews->firstItem() }}</td>
                  <td>{{ $r->bundle->name ?? '—' }}</td>
                  <td>{{ $r->rating }}/5</td>
                  <td>{{ Str::limit($r->comment, 60) }}</td>
                  <td>
                    <span class="badge {{ $r->active ? 'bg-success' : 'bg-secondary' }}">
                      {{ $r->active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>{{ $r->created_at->format('d M Y') }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center p-4">No reviews</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- pagination --}}
        <div class="card-footer">
          {{ $reviews->links() }}
        </div>
      </div>
    </div>

  </div>{{-- /tab-content --}}
@endsection
