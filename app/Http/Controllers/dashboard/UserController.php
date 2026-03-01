<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Exports\UsersExport;
use App\Models\Role;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
  // Display the user list with optional search
  public function index(Request $request)
  {
    $permissions = session('permissions');

    // Check if the user has 'read' permission for 'User Management'
    if (!isset($permissions['User Management']) || !in_array('read', $permissions['User Management']['actions'])) {
      return redirect()->route('dashboard-analytics')->with('error', 'You do not have permission to view users.');
    }

    // Get search parameters from the request
    $searchName = $request->input('name');
    $searchPhone = $request->input('phone');
    $searchRoleId = $request->input('role_id');
    $searchEmail = $request->input('email');
    $roles = Role::all();

    // Build the query with filters
    $users = User::when($searchName, function ($query, $searchName) {
      return $query->where('name', 'like', "%$searchName%");
    })
      ->when($searchPhone, function ($query, $searchPhone) {
        return $query->where('phone', 'like', "%$searchPhone%");
      })
      ->when($searchRoleId, function ($query, $searchRoleId) {
        return $query->where('role_id', $searchRoleId);
      })
      ->when($searchEmail, function ($query, $searchEmail) {
        return $query->where('email', 'like', "%$searchEmail%");
      })
      ->get();

    // Return the user list view with the filtered users
    return view('content.users.list', compact('users', 'roles'));
  }

  // Store a new user
  public function store(Request $request)
  {
    $permissions = session('permissions');

    // Check if the user has 'create' permission for 'User Management'
    if (!isset($permissions['User Management']) || !in_array('create', $permissions['User Management']['actions'])) {
      return redirect()->route('users.index')->with('error', 'You do not have permission to create users.');
    }

    // Validate the request data
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email',
      'phone' => 'nullable|string|max:15',
      'password' => 'required|string|min:8', // Ensure password confirmation
      'role_id' => 'required|exists:roles,id', // Ensure role exists in roles table
    ]);

    // Hash the password before storing
    $validated['password'] = bcrypt($validated['password']);

    // Create the user
    User::create($validated);

    // Flash a success message and trigger the toast
    return redirect()->route('users.index')->with('toast_success', 'User created successfully!');
  }

  // Update an existing user
  public function update(Request $request, $id)
  {
    $permissions = session('permissions');

    // Check if the user has 'write' or 'update' permission for 'User Management'
    if (!isset($permissions['User Management']) || !in_array('write', $permissions['User Management']['actions'])) {
      return redirect()->route('users.index')->with('error', 'You do not have permission to update users.');
    }

    $user = User::findOrFail($id);

    // Validate the request data
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email,' . $user->id,
      'phone' => 'nullable|string|max:15',
      'role_id' => 'required|exists:roles,id', // Ensure role exists in roles table
    ]);

    // If password is provided, hash it before saving
    if ($request->has('password') && !empty($request->password)) {
      $validated['password'] = bcrypt($request->password);
    }

    // Update the user
    $user->update($validated);

    // Flash a success message and trigger the toast
    return redirect()->route('users.index')->with('toast_success', 'User updated successfully!');
  }

  // Delete a user
  public function destroy($id)
  {
    $permissions = session('permissions');

    // Check if the user has 'delete' permission for 'User Management'
    if (!isset($permissions['User Management']) || !in_array('delete', $permissions['User Management']['actions'])) {
      return redirect()->route('users.index')->with('error', 'You do not have permission to delete users.');
    }

    $user = User::findOrFail($id);
    $user->delete();

    // Flash a success message and trigger the toast
    return redirect()->route('users.index')->with('toast_success', 'User deleted successfully!');
  }

  // Export users to Excel with filters
  public function export(Request $request)
  {
    $permissions = session('permissions');

    // Check if the user has 'read' permission for 'User Management'
    if (!isset($permissions['User Management']) || !in_array('read', $permissions['User Management']['actions'])) {
      return redirect()->route('users.index')->with('error', 'You do not have permission to export users.');
    }

    // Get search parameters from the request
    $searchName = $request->input('name');
    $searchPhone = $request->input('phone');
    $searchRoleId = $request->input('role_id');
    $searchEmail = $request->input('email');

    // Build the query with filters for export
    $users = User::when($searchName, function ($query, $searchName) {
      return $query->where('name', 'like', "%$searchName%");
    })
      ->when($searchPhone, function ($query, $searchPhone) {
        return $query->where('phone', 'like', "%$searchPhone%");
      })
      ->when($searchRoleId, function ($query, $searchRoleId) {
        return $query->where('role_id', $searchRoleId);
      })
      ->when($searchEmail, function ($query, $searchEmail) {
        return $query->where('email', 'like', "%$searchEmail%");
      })
      ->get();

    // Export the filtered users to Excel
    return Excel::download(new UsersExport($users), 'users.xlsx');
  }
}
