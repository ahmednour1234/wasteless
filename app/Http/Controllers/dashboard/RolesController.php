<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
  public function index()
  {
    $roles = Role::all();
    return view('content.users.roles', compact('roles'));
  }

  public function store(Request $request)
  {
    // Validate the request
    $request->validate([
      'name' => 'required|string|max:255',
      'permissions' => 'required|array',
      'permissions.*.name' => 'nullable|string',
      'permissions.*.actions' => 'array',
      'permissions.*.actions.*' => 'in:read,write,create',
    ]);
    // dd($request->all());
    // Save role with permissions
    $role = new Role();
    $role->name = $request->name;
    $role->data = json_encode($request->permissions); // Store permissions as JSON
    $role->save();

    return redirect()->back()->with('toast_success', 'Role created successfully!');
  }
  public function update(Request $request, $id)
  {
    // Validate the request
    $request->validate([
      'name' => 'required|string|max:255',
      'permissions' => 'required|array',
      'permissions.*.actions' => 'array',
      'permissions.*.actions.*' => 'in:read,write,create',
    ]);

    // Find and update the role
    $role = Role::findOrFail($id);
    $role->name = $request->name;
    $role->data = json_encode($request->permissions);
    $role->save();

    return redirect()->back()->with('toast_success', 'Role updated successfully!');
  }


  public function destroy($id)
  {
    // Find and delete the role
    $role = Role::findOrFail($id);
    $role->delete();

    return redirect()->back()->with('toast_success', 'Role deleted successfully!');
  }
}
