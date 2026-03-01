@extends('layouts/layoutMaster')

@section('title', 'Transactions')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endsection

@section('page-style')
<style>
  .pagination .page-item.active .page-link {
    background-color: #696cff;
    border-color: #696cff;
  }
  .pagination .page-link {
    color: #696cff;
    border-radius: 6px;
    margin: 0 2px;
  }
  .badge-status-pending { background-color: #ffc107; color: #000; }
  .badge-status-success { background-color: #28a745; color: #fff; }
  .badge-status-failed { background-color: #dc3545; color: #fff; }
  .badge-status-cancelled { background-color: #6c757d; color: #fff; }
</style>
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    $('.select2').select2({
      width: '100%',
      placeholder: '-- Select --'
    });
  });
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">All Transactions</h5>
    <form method="GET" action="{{ route('transactions.index') }}" class="w-100">
      <div class="row g-2">
        <div class="col-md-3">
          <select name="status" class="form-select select2">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="payment_type" class="form-select select2">
            <option value="">All Payment Types</option>
            <option value="whish_money" {{ request('payment_type') == 'whish_money' ? 'selected' : '' }}>Whish Money</option>
            <option value="omt_pay" {{ request('payment_type') == 'omt_pay' ? 'selected' : '' }}>OMT Pay</option>
            <option value="bank" {{ request('payment_type') == 'bank' ? 'selected' : '' }}>Bank</option>
          </select>
        </div>
        <div class="col-md-3">
          <input type="text" name="external_id" class="form-control" placeholder="External ID" value="{{ request('external_id') }}">
        </div>
        <div class="col-md-3 d-grid">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card-body table-responsive">
    <table class="table table-hover table-striped">
      <thead class="table-light">
        <tr class="text-center">
          <th>#</th>
          <th>External ID</th>
          <th>Payment Type</th>
          <th>Amount</th>
          <th>Currency</th>
          <th>Status</th>
          <th>Collect Status</th>
          <th>Payer Phone</th>
          <th>Order ID</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($transactions as $transaction)
        <tr class="text-center">
          <td>{{ $transaction->id }}</td>
          <td><code>{{ $transaction->external_id }}</code></td>
          <td>
            <span class="badge bg-info text-capitalize">
              {{ str_replace('_', ' ', $transaction->payment_type) }}
            </span>
          </td>
          <td class="text-success fw-bold">{{ number_format($transaction->amount, 2) }}</td>
          <td>{{ $transaction->currency }}</td>
          <td>
            <span class="badge badge-status-{{ $transaction->status }} text-capitalize">
              {{ $transaction->status }}
            </span>
          </td>
          <td>
            @if($transaction->collect_status)
              <span class="badge bg-{{ $transaction->collect_status == 'success' ? 'success' : ($transaction->collect_status == 'failed' ? 'danger' : 'warning') }}">
                {{ $transaction->collect_status }}
              </span>
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          <td>{{ $transaction->payer_phone_number ?? '-' }}</td>
          <td>
            @if($transaction->order_id)
              <a href="{{ route('orders.show', $transaction->order_id) }}" class="btn btn-sm btn-outline-primary">
                #{{ $transaction->order_id }}
              </a>
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
          <td>
            <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-sm btn-outline-info">View</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="11" class="text-center">No transactions found</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <div class="d-flex justify-content-center mt-4">
      {{ $transactions->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>
@endsection
