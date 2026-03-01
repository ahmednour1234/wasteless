@extends('layouts/layoutMaster')

@section('title', 'Analytics')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/swiper/swiper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/cards-advance.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
<script src="{{asset('assets/vendor/libs/swiper/swiper.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Edit Category</h4>
    </div>

    <div class="card-body">
        <form action="{{ route('category.update', $category) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            {{-- Name --}}
            <div class="mb-3">
                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text"
                       name="name"
                       id="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $category->name) }}"
                       required>
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            {{-- Image --}}
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file"
                       name="image"
                       id="image"
                       class="form-control @error('image') is-invalid @enderror"
                       accept="image/*">

                {{-- current preview --}}
                @if($category->image)
                    <div class="mt-2">
                        <img src="{{ asset($category->image) }}"
                             alt="Current Image"
                             width="70">
                    </div>
                @endif

                @error('image') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('category.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
