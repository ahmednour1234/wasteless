@extends('layouts.layoutMaster')

@section('title', 'Login')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">
      <!-- Login -->
      <div class="card">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-4 mt-2">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
              <span class="app-brand-logo demo">
                @include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])
              </span>
              @php
                  $templateName = DB::table('settings')->value('name');
              @endphp
              <span class="app-brand-text demo text-body fw-bold ms-1">{{ $templateName }}</span>
            </a>
          </div>
          <!-- /Logo -->

          <h4 class="mb-1 pt-2 text-center">Welcome to the Admin Panel 👋</h4>
          <p class="mb-4 text-center">Enter your admin email and password to log in.</p>

          <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-3 text-start">
              <label for="email-username" class="form-label">Email</label>
              <input
                type="text"
                class="form-control"
                id="email-username"
                name="email-username"
                placeholder="Enter your email"
                value="{{ old('email-username') }}"
                required
                autofocus
              >
              @error('email-username')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="mb-3 form-password-toggle text-start">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control"
                  name="password"
                  placeholder="••••••••"
                  required
                />
                <span class="input-group-text cursor-pointer">
                  <i class="ti ti-eye-off"></i>
                </span>
              </div>
            </div>

            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100" type="submit">
                Log In
              </button>
            </div>
          </form>
        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>
</div>

@endsection
