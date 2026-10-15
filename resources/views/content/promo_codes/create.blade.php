@extends('layouts/layoutMaster')

@section('title', 'Add Promo Code')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Add New Promo Code</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('promo-codes.store') }}" method="POST">
            @include('content.promo_codes._form')
        </form>
    </div>
</div>
@endsection
