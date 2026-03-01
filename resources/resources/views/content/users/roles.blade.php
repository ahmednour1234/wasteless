@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Roles - Apps')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />
@endsection


@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
  <h4 class="mb-4">@lang("Roles List")</h4>
<!-- Role cards -->
<div class="row g-4">
  <div class="col-xl-12 col-lg-12 col-md-12">
    <div class="card h-100">
      <div class="row h-100">
        <div class="col-sm-5">
          <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-3">
            <img src="{{ asset('assets/img/illustrations/add-new-roles.png') }}" class="img-fluid mt-sm-4 mt-md-0" alt="@lang('Add New Role Image')" width="83">
          </div>
        </div>
        <div class="col-sm-7">
          <div class="card-body text-sm-end text-center ps-sm-0">
            <button data-bs-target="#addRoleModal" data-bs-toggle="modal" class="btn btn-primary mb-2 text-nowrap add-new-role">@lang("Add New Role")</button>
            <p class="mb-0 mt-1">@lang("Add role, if it does not exist")</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <!-- Role Table -->
    <div class="card">
      <h5 class="card-header">@lang("Roles Table")</h5>
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
          <tr>
            <th>@lang("Role Name")</th>
            <th>@lang("Actions")</th>
          </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach ($roles as $role )
            <tr>
              <td>
                <span class="fw-medium">{{ $role->name }}</span>
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="ti ti-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu">
                    <!-- Edit Role Action -->
                    <a class="dropdown-item" data-bs-target="#editRoleModal" data-bs-toggle="modal" href="javascript:void(0);"
                       onclick="openEditRoleModal({{ json_encode($role) }})">
                      <i class="ti ti-pencil me-1"></i> @lang("Edit")
                    </a>

                    <!-- Delete Role Action -->
                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" id="deleteRoleForm{{ $role->id }}" style="display: none;">
                      @csrf
                      @method('DELETE')
                    </form>
                    <a class="dropdown-item" href="javascript:void(0);"
                       onclick="confirmDelete({{ $role->id }})">
                      <i class="ti ti-trash me-1"></i> @lang("Delete")
                    </a>
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <!--/ Role Table -->
  </div>
</div>
<!--/ Role cards -->


  <!-- Add Role Modal -->
  @include('_partials/_modals/modal-add-role')
  @include('_partials/_modals/modal-edit-role')


  <!-- / Add Role Modal -->
@endsection
<script>
  function openEditRoleModal(role) {
  const form = document.getElementById('editRoleForm');
  form.action = `/roles/${role.id}`; // Update form action with role ID
  document.getElementById('editRoleName').value = role.name;

  // Clear existing permissions
  const checkboxes = document.querySelectorAll('#editRoleForm .edit-permission-checkbox');
  checkboxes.forEach(checkbox => checkbox.checked = false);

  // Populate permissions
  if (role.data) {
    const permissions = JSON.parse(role.data);
    for (const [permission, actions] of Object.entries(permissions)) {
      actions.forEach(action => {
        const checkbox = document.getElementById(`edit${permission}${action}`);
        if (checkbox) {
          checkbox.checked = true;
        }
      });
    }
  }

  // Show the modal
  new bootstrap.Modal(document.getElementById('editRoleModal')).show();
}
function confirmDelete(roleId) {
  if (confirm('Are you sure you want to delete this role?')) {
    document.getElementById(`deleteRoleForm${roleId}`).submit();
  }
}

  </script>
