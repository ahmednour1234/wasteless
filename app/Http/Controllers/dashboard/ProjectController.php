<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Pdf;
use App\Models\Project;
use App\Models\Setting;
use Endroid\QrCode\QrCode;  // Import the Endroid QrCode class
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage; // Add this import
use Illuminate\Http\Request;

class ProjectController extends Controller
{
  /**
   * Display a listing of the projects.
   */
  public function index()
  {
    $permissions = session('permissions');
    if (!isset($permissions['Projects']) || !in_array('read', $permissions['Projects']['actions'])) {
      abort(403, 'Unauthorized action.');
    }

$projects = Project::orderBy('created_at', 'desc')->get();
    return view('content.projects.index', compact('projects')); // Return a view for listing projects
  }

  /**
   * Store a newly created project in storage.
   */

public function store(Request $request)
{
    $permissions = session('permissions');
    if (!isset($permissions['Projects']) || !in_array('create', $permissions['Projects']['actions'])) {
        abort(403, 'Unauthorized action.');
    }

    // Validate the incoming request data
  $request->validate([
    'name' => 'required|string|max:255',
    'name_ar' => 'nullable|string|max:255',
    'title' => 'required|string|max:255',
    'title_ar' => 'nullable|string|max:255',
]);

// If 'name_ar' is provided, set 'name' to 'name_ar'
if ($request->has('name')) {
    $request->merge(['name_ar' => $request->input('name')]);
}
if ($request->has('title')) {
    $request->merge(['title_ar' => $request->input('title')]);
}

// Create the project using the updated data
$project = Project::create($request->all());


    // Generate the URL for the QR code (route with 'id' parameter)
    $qrcodeData = route('showpublic', ['id' => $project->id]);

    // Create a new QR code instance
    $qrCode = new QrCode($qrcodeData);
    $writer = new PngWriter();

    // Generate the QR code image as a string (PNG format)
    $qrcodeImage = $writer->write($qrCode)->getString();

    // Define the path to save the QR code image in the public directory
    $qrcodePath = 'qrcodes/project-' . $project->id . '.png';

    // Ensure the directory exists in the public folder
    if (!file_exists(public_path('qrcodes'))) {
        mkdir(public_path('qrcodes'), 0755, true);
    }

    // Save the QR code image to the public directory
    file_put_contents(public_path($qrcodePath), $qrcodeImage);

    // Update the project with the QR code path in the database
    $project->update(['qrcode' => $qrcodePath]);

    // Return a success message and redirect back
    return redirect()->back()->with('success', 'Project created successfully with QR Code!');
}


  /**
   * Display the specified project.
   */
  public function show($id)
  {

    $permissions = session('permissions');
    if (!isset($permissions['Pdfs']) || !in_array('read', $permissions['Pdfs']['actions'])) {
      abort(403, 'Unauthorized action.');
    }
    $project = Project::find($id);
$pdfs = Pdf::where('project_id', $id)
    ->orderBy('created_at', 'desc')
    ->get();


    if (!$project) {
      return redirect()->back()->with('error', 'Project not found!');
    }

    return view('content.pdfs.show', compact('project', 'pdfs')); // Return a view for showing project details
  }
 public function showpublic($id)
{
    $project = Project::find($id);
    $pdfs = Pdf::where('project_id', $id)->get();
    $settings = Setting::first(); // Assuming a single settings entry exists

    if (!$project) {
        return redirect()->back()->with('error', 'Project not found!');
    }

    return view('content.pdfs.showpublic', compact('project', 'pdfs', 'settings'));
}

  /**
   * Update the specified project in storage.
   */
  public function update(Request $request, $id)
  {
    $permissions = session('permissions');
    if (!isset($permissions['Projects']) || !in_array('write', $permissions['Projects']['actions'])) {
      abort(403, 'Unauthorized action.');
    }
    $project = Project::find($id);

    if (!$project) {
      return redirect()->back()->with('error', 'Project not found!');
    }

    $request->validate([
      'name' => 'sometimes|required|string|max:255',
      'name_ar' => 'sometimes|required|string|max:255',
      'title' => 'sometimes|required|string|max:255',
      'title_ar' => 'sometimes|required|string|max:255',
    ]);

    $project->update($request->all());

    return redirect()->back()->with('success', 'Project updated successfully!');
  }

  /**
   * Remove the specified project from storage.
   */
  public function destroy($id)
  {
    $permissions = session('permissions');
    if (!isset($permissions['Projects']) || !in_array('write', $permissions['Projects']['actions'])) {
      abort(403, 'Unauthorized action.');
    }
    $project = Project::find($id);

    if (!$project) {
      return redirect()->back()->with('error', 'Project not found!');
    }

    $project->delete();

    return redirect()->back()->with('success', 'Project deleted successfully!');
  }
}
