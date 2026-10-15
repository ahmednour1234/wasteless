@extends('layouts/layoutMaster')

@section('title', 'Edit Promo Code')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Edit Promo Code: {{ $promoCode->code }}</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('promo-codes.update', $promoCode) }}" method="POST">
            @method('PUT')
            @include('content.promo_codes._form')
        </form>
    </div>
</div>
@endsection
