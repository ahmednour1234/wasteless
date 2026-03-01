@extends('layouts/layoutMaster')

@section('title', 'User List - Pages')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />
  <!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

<!-- Toastr JS -->

@endsection

@section('page-script')
  <script src="{{asset('assets/js/app-user-list.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
@endsection

@section('content')
  <!-- Add success and error notifications -->
  @if(session('toast_success'))
  <script>
    toastr.success('{{ session('toast_success') }}');
  </script>
@endif

@if(session('toast_error'))
  <script>
    toastr.error('{{ session('toast_error') }}');
  </script>
@endif

  <!-- User Management Page -->

<!-- Filter and Search Data Section -->
<div class="card mb-4">
  <h5 class="card-header">@lang("Filter and Search Data")</h5>
  <form class="card-body" action="{{ route('users.index') }}" method="GET">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label" for="multicol-username">@lang("Name")</label>
        <input type="text" name="name" id="multicol-username" class="form-control" placeholder="@lang('Enter name')" />
      </div>
      <div class="col-md-6">
        <label class="form-label" for="multicol-email">@lang("Email")</label>
        <div class="input-group input-group-merge">
          <input type="text" name="email" id="multicol-email" class="form-control" placeholder="@lang('Enter email')" />
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="multicol-phone">@lang("Phone")</label>
        <div class="input-group input-group-merge">
          <input type="text" name="phone" id="multicol-phone" class="form-control" placeholder="@lang('Enter phone')" />
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="role">@lang("Role")</label>
        <select id="role" name="role_id" class="select2 form-select" data-allow-clear="true">
          <option value="">@lang("Select Role")</option>
          @foreach ($roles as $role )
            <option value="{{ $role->id }}">{{ $role->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="pt-4 row g-3">
      <button type="submit" class="btn btn-primary me-sm-3 me-1">@lang("Filter")</button>
    </form>
    <form action="{{ route('users.export') }}" method="GET">
      <input type="hidden" name="name" value="{{ request('name') }}">
      <input type="hidden" name="phone" value="{{ request('phone') }}">
      <input type="hidden" name="role_id" value="{{ request('role_id') }}">
      <input type="hidden" name="email" value="{{ request('email') }}">

      <button type="submit" class="btn btn-label-secondary">@lang("Export Excel")</button>
    </form>
    </div>
</div>

<!-- Add New User Button -->
<button style="width: 100%; margin: 20px 0;" class="add-new btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addUserModal">
  <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">@lang("Add New User")</span>
</button>

<!-- Users Table -->
<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>@lang("Name")</th>
          <th>@lang("Role")</th>
          <th>@lang("Actions")</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach ($users as $user)
          <tr>
            <td><span class="fw-medium">{{ $user->name }}</span></td>
            <td>{{ $user->role->name }}</td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#updateUserModal{{ $user->id }}"><i class="ti ti-pencil me-1"></i> @lang("Edit")</a>
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}"><i class="ti ti-trash me-1"></i> @lang("Delete")</a>
                </div>
              </div>
            </td>
          </tr>

          <!-- Update User Modal -->
          <div class="modal fade" id="updateUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="updateUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="updateUserModalLabel">@lang("Update User")</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
                </div>
                <div class="modal-body">
                  <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                      <label class="form-label" for="update-user-fullname">@lang("Full Name")</label>
                      <input type="text" class="form-control" id="update-user-fullname" name="name" value="{{ $user->name }}" required />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="update-user-email">@lang("Email")</label>
                      <input type="email" class="form-control" id="update-user-email" name="email" value="{{ $user->email }}" required />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="update-user-password">@lang("Password")</label>
                      <input type="password" class="form-control" id="update-user-password" name="password" placeholder="@lang('Enter new password')" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="update-user-contact">@lang("Phone")</label>
                      <input type="text" class="form-control" id="update-user-contact" name="phone" value="{{ $user->phone }}" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label" for="update-user-role">@lang("Role")</label>
                      <select class="form-select" id="update-user-role" name="role_id" required>
                        @foreach ($roles as $role )
                          <option value="{{ $role->id }}" {{ $role->id == $user->role_id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <button type="submit" class="btn btn-primary">@lang("Update")</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang("Cancel")</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Delete Confirmation Modal -->
          <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteModalLabel">@lang("Confirm Deletion")</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
                </div>
                <div class="modal-body">
                  @lang("Are you sure you want to delete the user") {{ $user->name }}?
                </div>
                <div class="modal-footer">
                  <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">@lang("Delete")</button>
                  </form>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang("Cancel")</button>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">@lang("Add User")</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('users.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="add-user-fullname">@lang("Full Name")</label>
            <input type="text" class="form-control" id="add-user-fullname" name="name" required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-user-email">@lang("Email")</label>
            <input type="email" class="form-control" id="add-user-email" name="email" required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-user-password">@lang("Password")</label>
            <input type="password" class="form-control" id="add-user-password" name="password" required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-user-contact">@lang("Phone")</label>
            <input type="text" class="form-control" id="add-user-contact" name="phone" required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="add-user-role">@lang("Role")</label>
            <select class="form-select" id="add-user-role" name="role_id" required>
              @foreach ($roles as $role )
                <option value="{{ $role->id }}">{{ $role->name }}</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary">@lang("Add")</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang("Cancel")</button>
        </form>
      </div>
    </div>
  </div>
</div>


@endsection
