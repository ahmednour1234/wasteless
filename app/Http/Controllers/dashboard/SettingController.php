<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
  /**
   * Show the settings form.
   *
   * @return \Illuminate\View\View
   */
  public function index()
  {
    $this->authorizeAction('read'); // Check permission for 'read'

    $settings = Setting::first(); // Retrieve the first settings record if it exists
    return view('content.setting.setting', compact('settings'));
  }

  /**
   * Store or update the settings.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
public function store(Request $request)
{
    // Validate the request data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string',
        'img' => 'nullable', // Validate image (nullable, max 2MB)
        'address' => 'nullable',
        'commission_percentage' => 'nullable|numeric|min:0|max:100',
    ]);

    // Check if settings record exists
    $settings = Setting::first();

    // Handle image upload
    if ($request->hasFile('img')) {
        // Get the image file
        $image = $request->file('img');

        // Generate a unique name for the image file to avoid overwriting
        $imageName = time() . '-' . $image->getClientOriginalName();

        // If there is an existing image, delete the old one
        if ($settings && $settings->img) {
            $oldImagePath = public_path($settings->img); // Path of the old image
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath); // Delete the old image file
            }
        }

        // Move the new image to the public directory (directly in the public folder)
        $image->move(public_path(), $imageName);

        // Add the image path to the validated data
        $validated['img'] = $imageName; // Store the image name directly (no subfolder)
    }

    // Update existing settings or create a new one
    if ($settings) {
        // Update existing settings
        $settings->update($validated);
    } else {
        // Create new settings record
        Setting::create($validated);
    }

    // Redirect back with success message
    return redirect()->back()->with('success', 'Settings saved successfully!');
}

  /**
   * Check if the user has permission for the specified action.
   *
   * @param string $action
   * @return void
   */
  private function authorizeAction(string $action)
  {
    $permissions = session('permissions');

    // Check if user has permission for the requested action
    if (!isset($permissions['Setting Management']) || !in_array($action, $permissions['Setting Management']['actions'])) {
      abort(403, 'Unauthorized action.');
    }
  }
}
