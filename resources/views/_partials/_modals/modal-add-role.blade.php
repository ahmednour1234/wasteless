<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="role-title mb-2">@lang("Add New Role")</h3>
          <p class="text-muted">@lang("Set role permissions")</p>
        </div>
        <!-- Add role form -->
        <form id="addRoleForm" class="row g-3" action="{{ route('roles.store') }}" method="POST">
          @csrf
          <div class="col-12 mb-4">
            <label class="form-label" for="roleName">@lang("Role Name")</label>
            <input type="text" id="roleName" name="name" class="form-control" placeholder="@lang('Enter a role name')" required />
          </div>
          <div class="col-12">
            <h5>@lang("Role Permissions")</h5>
            <!-- Permission table -->
            <div class="table-responsive">
              <table class="table table-flush-spacing">
                <tbody>
                  <tr>
                    <td class="text-nowrap fw-medium">@lang("Administrator Access")</td>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllPermissions" onclick="toggleAllPermissions()" />
                        <label class="form-check-label" for="selectAllPermissions">
                          @lang("Select All")
                        </label>
                      </div>
                    </td>
                  </tr>
                  @foreach(['User Management', 'Roles Management','Company', 'Branch','Category','Bundle','Customer','Review','Setting Management', 'Dashboard Management'] as $permission)
                  <tr>
                    <td class="text-nowrap fw-medium">@lang($permission)</td>
                    <td>
                      <div class="d-flex">
                        @foreach(['read', 'write', 'create'] as $action)
                        <div class="form-check me-3">
                          <input class="form-check-input" type="checkbox" name="permissions[{{ $permission }}][actions][]" value="{{ $action }}" id="{{ $permission . $action }}" />
                          <label class="form-check-label" for="{{ $permission . $action }}">@lang(ucfirst($action))</label>
                        </div>
                        @endforeach
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">@lang("Submit")</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="@lang('Close')">@lang("Cancel")</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script defer>
  // Function to toggle all permission checkboxes
  function toggleAllPermissions() {
    const checkboxes = document.querySelectorAll('#addRoleForm input[type="checkbox"]:not(#selectAllPermissions)');
    const selectAll = document.getElementById('selectAllPermissions');

    // Update checkboxes state based on "select all" checkbox
    checkboxes.forEach(checkbox => {
      checkbox.checked = selectAll.checked;
    });
  }

  // Ensure the toggleAllPermissions runs when the page loads
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize the toggle permissions function
    toggleAllPermissions();
  });
</script>
