@extends('layouts/layoutMaster')

@section('title', 'Transaction Details')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Transaction Details #{{ $transaction->id }}</h5>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Back to List</a>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <div class="col-md-6">
            <h6 class="text-muted mb-3">Transaction Information</h6>
            <table class="table table-borderless">
              <tr>
                <th width="40%">External ID:</th>
                <td><code>{{ $transaction->external_id }}</code></td>
              </tr>
              <tr>
                <th>Payment Type:</th>
                <td>
                  <span class="badge bg-info text-capitalize">
                    {{ str_replace('_', ' ', $transaction->payment_type) }}
                  </span>
                </td>
              </tr>
              <tr>
                <th>Amount:</th>
                <td class="text-success fw-bold">{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</td>
              </tr>
              <tr>
                <th>Status:</th>
                <td>
                  <span class="badge badge-status-{{ $transaction->status }} text-capitalize">
                    {{ $transaction->status }}
                  </span>
                </td>
              </tr>
              <tr>
                <th>Collect Status:</th>
                <td>
                  @if($transaction->collect_status)
                    <span class="badge bg-{{ $transaction->collect_status == 'success' ? 'success' : ($transaction->collect_status == 'failed' ? 'danger' : 'warning') }}">
                      {{ $transaction->collect_status }}
                    </span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>Payer Phone:</th>
                <td>{{ $transaction->payer_phone_number ?? '-' }}</td>
              </tr>
              <tr>
                <th>Invoice:</th>
                <td>{{ $transaction->invoice ?? '-' }}</td>
              </tr>
              <tr>
                <th>Created At:</th>
                <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
              </tr>
              <tr>
                <th>Updated At:</th>
                <td>{{ $transaction->updated_at->format('Y-m-d H:i:s') }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-3">Order Information</h6>
            @if($transaction->order_id)
              <table class="table table-borderless">
                <tr>
                  <th width="40%">Order ID:</th>
                  <td>
                    <a href="{{ route('orders.show', $transaction->order_id) }}" class="btn btn-sm btn-outline-primary">
                      #{{ $transaction->order_id }}
                    </a>
                  </td>
                </tr>
                @if($transaction->order)
                  <tr>
                    <th>Customer:</th>
                    <td>{{ $transaction->order->name }}</td>
                  </tr>
                  <tr>
                    <th>Phone:</th>
                    <td>{{ $transaction->order->phone }}</td>
                  </tr>
                  <tr>
                    <th>Address:</th>
                    <td>{{ $transaction->order->address ?? '-' }}</td>
                  </tr>
                  <tr>
                    <th>Order Status:</th>
                    <td>
                      <span class="badge bg-info text-capitalize">{{ $transaction->order->status }}</span>
                    </td>
                  </tr>
                @endif
              </table>
            @else
              <p class="text-muted">No order associated with this transaction</p>
            @endif

            <h6 class="text-muted mb-3 mt-4">Payment URLs</h6>
            <table class="table table-borderless">
              <tr>
                <th width="40%">Collect URL:</th>
                <td>
                  @if($transaction->collect_url)
                    <a href="{{ $transaction->collect_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>Success Callback:</th>
                <td><small class="text-muted">{{ $transaction->success_callback_url }}</small></td>
              </tr>
              <tr>
                <th>Failure Callback:</th>
                <td><small class="text-muted">{{ $transaction->failure_callback_url }}</small></td>
              </tr>
            </table>
          </div>
        </div>

        @if($transaction->order && $transaction->order->details)
          <div class="row">
            <div class="col-12">
              <h6 class="text-muted mb-3">Order Items</h6>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="table-light">
                    <tr>
                      <th>Bundle</th>
                      <th>Quantity</th>
                      <th>Price</th>
                      <th>Discount</th>
                      <th>Total</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($transaction->order->details as $detail)
                    <tr>
                      <td>
                        @if($detail->bundle)
                          {{ $detail->bundle->name }}
                        @else
                          Bundle #{{ $detail->bundle_id }}
                        @endif
                      </td>
                      <td>{{ $detail->quantity }}</td>
                      <td>{{ number_format($detail->price, 2) }} LBP</td>
                      <td class="text-danger">-{{ number_format($detail->discount, 2) }} LBP</td>
                      <td class="text-success">{{ number_format($detail->total, 2) }} LBP</td>
                      <td>
                        <span class="badge bg-info text-capitalize">{{ $detail->status }}</span>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif

        @if($transaction->metadata)
          <div class="row mt-4">
            <div class="col-12">
              <h6 class="text-muted mb-3">Metadata</h6>
              <pre class="bg-light p-3 rounded"><code>{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
