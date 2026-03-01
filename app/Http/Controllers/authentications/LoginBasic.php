<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LoginBasic extends Controller
{
  // Show the login page
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
  }

  // Handle the login attempt
  public function login(Request $request)
  {
    // Validate the incoming login request
    $request->validate([
      'email-username' => 'required|string',  // Email or Username
      'password' => 'required|string',
    ]);

    // Retrieve the login data (email or username)
    $login = $request->input('email-username');
    $password = $request->input('password');

    // Find the user by email or username
    $user = User::where('email', $login)->orWhere('name', $login)->first();

    if ($user && Hash::check($password, $user->password)) {
      $role = $user->role;  // Assuming the user has a relationship with the Role model
      $permissions = json_decode($role->data, true);

      // Store the permissions in the session or as part of the user data
      session(['permissions' => $permissions]);
      Auth::login($user, $request->has('remember'));  // "remember" option is passed here
      return redirect()->route('dashboard-analytics');  // Redirect to the desired page
    }

    // If credentials are invalid, throw validation exception
    throw ValidationException::withMessages([
      'email-username' => ['The provided credentials are incorrect.'],
    ]);
  }

  // Handle logout
  public function logout(Request $request)
  {
    Auth::logout(); // Log out the user
    $request->session()->invalidate();  // Invalidate the session
    $request->session()->regenerateToken();  // Regenerate the CSRF token to prevent attacks

    return redirect()->route('login');  // Redirect to login page
  }
}
