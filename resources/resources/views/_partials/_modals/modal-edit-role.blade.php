<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-edit-role">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="role-title mb-2">Edit Role</h3>
          <p class="text-muted">Update role permissions</p>
        </div>
        <!-- Edit role form -->
        <form id="editRoleForm" class="row g-3" action="" method="POST">
          @csrf
          @method('PUT')
          <div class="col-12 mb-4">
            <label class="form-label" for="editRoleName">Role Name</label>
            <input type="text" id="editRoleName" name="name" class="form-control"
                   placeholder="Enter role name" value="{{ old('name', isset($role) ? $role->name : '') }}" required />
          </div>
          <div class="col-12">
            <h5>Role Permissions</h5>
            <!-- Permission table -->
            <div class="table-responsive">
              <table class="table table-flush-spacing">
                <tbody>
                  <tr>
                    <td class="text-nowrap fw-medium">Administrator Access</td>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="editSelectAllPermissions" onclick="toggleEditAllPermissions()" />
                        <label class="form-check-label" for="editSelectAllPermissions">
                          Select All
                        </label>
                      </div>
                    </td>
                  </tr>
                  @foreach(['User Management', 'Roles Management','Company', 'Branch','Category' ,'Bundle','Setting Management', 'Dashboard Management'] as $permission)
                  <tr>
                    <td class="text-nowrap fw-medium">{{ $permission }}</td>
                    <td>
                      <div class="d-flex">
                        @foreach(['read', 'write', 'create'] as $action)
                        <div class="form-check me-3">
                          <input class="form-check-input edit-permission-checkbox" type="checkbox"
                                 name="permissions[{{ $permission }}][actions][]"
                                 value="{{ $action }}"
                                 id="edit{{ $permission . $action }}"
                                 @if(isset($role->permissions[$permission]) && in_array($action, $role->permissions[$permission]['actions'])) checked @endif />
                          <label class="form-check-label" for="edit{{ $permission . $action }}">{{ ucfirst($action) }}</label>
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
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Update</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script defer>
  // Function to toggle all permission checkboxes for the edit modal
  function toggleEditAllPermissions() {
    const checkboxes = document.querySelectorAll('#editRoleForm .edit-permission-checkbox');
    const selectAll = document.getElementById('editSelectAllPermissions');

    // Update checkboxes state based on "select all" checkbox
    checkboxes.forEach(checkbox => {
      checkbox.checked = selectAll.checked;
    });
  }

  // Populate the edit modal with role data
  function showEditModal(role) {
    const form = document.getElementById('editRoleForm');
    form.action = `/roles/${role.id}`; // Update form action with role ID
    document.getElementById('editRoleName').value = role.name;

    // Clear existing permissions
    const checkboxes = document.querySelectorAll('#editRoleForm .edit-permission-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = false);

    // Parse permissions from the role and populate checkboxes
    if (role.permissions) {
      const permissions = JSON.parse(role.permissions); // Assuming role.permissions is a JSON string, parse it
      for (const [permission, actions] of Object.entries(permissions)) {
        actions.forEach(action => {
          const checkbox = document.getElementById(`edit${permission}${action}`);
          if (checkbox) {
            checkbox.checked = true;  // Check the relevant checkbox based on actions
          }
        });
      }
    }

    // Show modal
    new bootstrap.Modal(document.getElementById('editRoleModal')).show();
  }
</script>
