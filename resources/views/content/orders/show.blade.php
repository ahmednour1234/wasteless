@extends('layouts/layoutMaster')

@section('title', 'Order Invoice')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/swiper/swiper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/cards-advance.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
<script src="{{asset('assets/vendor/libs/swiper/swiper.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('content')
<div class="container" id="invoice-content">
    <!-- Invoice Header -->
    <div class="card mb-4 border border-primary shadow-sm">
        <div class="card-body border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 text-primary">Invoice #{{ $order->id }}</h4>
                <small class="text-muted">Date: {{ $order->created_at->format('Y-m-d H:i') }}</small>
            </div>
            <span class="badge bg-primary text-uppercase px-3 py-2">{{ ucfirst($order->status) }}</span>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="text-dark mb-3">Customer Information</h5>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <strong>Name:</strong>
                    <div>{{ $order->name }}</div>
                </div>
                <div class="col-md-4 mb-2">
                    <strong>Phone:</strong>
                    <div>{{ $order->phone }}</div>
                </div>
                <div class="col-md-4 mb-2">
                    <strong>Address:</strong>
                    <div>{{ $order->address }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-4 text-center">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-start border-primary border-4">
                <strong>Subtotal</strong>
                <div class="fs-5 text-primary">{{ number_format($order->sub_total, 2) }} Lpb</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-start border-danger border-4">
                <strong>Discount</strong>
                <div class="fs-5 text-danger">{{ number_format($order->total_discount, 2) }} USD</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-start border-success border-4">
                <strong>Total</strong>
                <div class="fs-5 text-success">
                    {{ number_format($order->sub_total + $order->delivery - $order->total_discount, 2) }} USD
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">Order Items</h6>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th>#</th>
                        <th>Bundle</th>
                        <th>Store</th>
                        <th>Branch</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->details as $index => $detail)
                        <tr class="text-center align-middle">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $detail->bundle->name ?? '—' }}</td>
                            <td>{{ $detail->bundle->company->name ?? '—' }}</td>
                            <td>{{ $detail->bundle->branch->name ?? '—' }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ number_format($detail->price, 2) }} USD</td>
                            <td>{{ number_format($detail->discount, 2) }} USD</td>
                            <td>{{ number_format($detail->total - ($detail->discount * $detail->quantity), 2) }} USD</td>
                            <td>
                                <span class="badge bg-warning text-dark text-capitalize">{{ $detail->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No order items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Print Button -->
    <div class="text-end mt-4">
        <button onclick="printInvoice()" class="btn btn-outline-dark">
            <i class="bx bx-printer me-1"></i> Print Invoice
        </button>
    </div>
</div>
@endsection

<script>
    function printInvoice() {
        const content = document.getElementById('invoice-content').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Invoice #{{ $order->id }}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: center; }
                        h4 { color: #333; margin-bottom: 5px; }
                        .badge { padding: 5px 10px; border-radius: 4px; }
                        .text-primary { color: #0d6efd; }
                        .text-danger { color: #dc3545; }
                        .text-success { color: #198754; }
                    </style>
                </head>
                <body>
                    ${content}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
</script>
