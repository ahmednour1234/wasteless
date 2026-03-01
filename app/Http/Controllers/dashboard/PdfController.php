<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Pdf;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class PdfController extends Controller
{
  // Store a new PDF record with QR code
public function store(Request $request)
{
    $permissions = session('permissions');
    if (!isset($permissions['Pdfs']) || !in_array('create', $permissions['Pdfs']['actions'])) {
        abort(403, 'Unauthorized action.');
    }

    // Validate the incoming request data
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'pdfs.*' => 'required|file|mimes:pdf|max:307200', // Validate PDF files, max 300MB
    ]);

    // Get the project associated with the project_id
    $project = Project::findOrFail($request->project_id);

    // Ensure directories for saving files exist
    if (!file_exists(public_path('pdfs'))) {
        mkdir(public_path('pdfs'), 0755, true);
    }
    if (!file_exists(public_path('qrcodes'))) {
        mkdir(public_path('qrcodes'), 0755, true);
    }

    $uploadedFiles = $request->file('pdfs');
    $errorMessages = [];

    foreach ($uploadedFiles as $pdfFile) {
        $pdfPath = 'pdfs/' . $pdfFile->getClientOriginalName();

        // Save the PDF file
        $pdfFile->move(public_path('pdfs'), $pdfFile->getClientOriginalName());

        // Extract text from the PDF
        $pdfText = null;
        try {
            $pdfText = (new \Smalot\PdfParser\Parser())->parseFile(public_path($pdfPath))->getText();
        } catch (\Exception $e) {
            $errorMessages[] = "Failed to read the PDF content for {$pdfFile->getClientOriginalName()}.";
            continue;
        }

        // Extract name and name_ar from the PDF text
        $name = $this->extractFieldFromPdf($pdfText, 'name');
        $name_ar = $this->extractFieldFromPdf($pdfText, 'name_ar');

        // If name extraction fails, use the file name (without extension)
        if (!$name) {
            $name = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
        }

        // If name_ar extraction fails, set it to null
        $name_ar = $name_ar ?: null;

        // Generate QR code URL for the project
        $qrcodeData = route('dashboard-project.show', ['id' => $project->id]);
        $qrCode = new QrCode($qrcodeData);
        $writer = new PngWriter();
        $qrcodeImage = $writer->write($qrCode)->getString();

        // Define the QR code path
        $qrcodePath = 'qrcodes/project-' . $project->id . '-' . time() . '.png';

        // Save the QR code image
        file_put_contents(public_path($qrcodePath), $qrcodeImage);

        // Calculate PDF size in bytes
        $pdfSizeInBytes = filesize(public_path($pdfPath));

        // Create the PDF record in the database
        Pdf::create([
            'project_id' => $project->id,
            'name' => $name,
            'name_ar' => $name_ar,
            'qrcode' => $qrcodePath,
            'pdf' => $pdfPath,
            'size' => $pdfSizeInBytes,
        ]);
    }

    // Prepare a success message
    $successMessage = count($uploadedFiles) . " PDFs uploaded successfully!";
    if (!empty($errorMessages)) {
        $successMessage .= ' However, the following errors occurred: ' . implode(', ', $errorMessages);
    }

    // Return the response
    return redirect()->back()->with('success', $successMessage);
}

/**
 * Helper function to extract specific fields from PDF text.
 *
 * @param string $pdfText
 * @param string $field
 * @return string|null
 */
private function extractFieldFromPdf($pdfText, $field)
{
    $patterns = [
        'name' => '/Name:\s*(.+)/i', // Adjust the regex pattern based on the actual text in the PDF
        'name_ar' => '/Name \(AR\):\s*(.+)/i',
    ];

    if (!isset($patterns[$field])) {
        return null;
    }

    if (preg_match($patterns[$field], $pdfText, $matches)) {
        return trim($matches[1]);
    }

    return null;
}



  // Show the list of PDFs
  public function index()
  {
    $permissions = session('permissions');
    if (!isset($permissions['Pdfs']) || !in_array('read', $permissions['Pdfs']['actions'])) {
      abort(403, 'Unauthorized action.');
    }
    $pdfs = Pdf::all();
    return redirect()->back()->with('success', 'PDF updated successfully!');
  }

  // Show the form to edit an existing PDF
  public function edit($id)
  {
    $pdf = Pdf::findOrFail($id);
    $projects = Project::all(); // Get all projects to show in the form
    return view('dashboard.pdfs.edit', compact('pdf', 'projects'));
  }

  // Update an existing PDF record
public function update(Request $request, $id)
{
    $permissions = session('permissions');
    if (!isset($permissions['Pdfs']) || !in_array('write', $permissions['Pdfs']['actions'])) {
        abort(403, 'Unauthorized action.');
    }

    $pdf = Pdf::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'name_ar' => 'nullable|string|max:255',
        'pdf' => 'nullable|file|mimes:pdf|max:10240', // 10MB max, PDF file only
    ]);

    // Merge 'name' into 'name_ar' if provided
    if ($request->has('name')) {
        $request->merge(['name_ar' => $request->input('name')]);
    }

    // Handle PDF file upload if a new file is provided
    if ($request->hasFile('pdf')) {
        // Get file size in bytes
        $pdfSizeInBytes = $request->file('pdf')->getSize();

        // Convert size to KB and MB
        $pdfSizeInKB = round($pdfSizeInBytes / 1024, 2);  // in KB
        $pdfSizeInMB = round($pdfSizeInKB / 1024, 2);  // in MB

        // Optionally check the file size before storing it
        if ($pdfSizeInMB > 10) {
            return back()->withErrors(['pdf' => 'The PDF file size must be less than 10MB.']);
        }

        // Delete the old file if it exists
        if ($pdf->pdf_path && file_exists(public_path($pdf->pdf_path))) {
            unlink(public_path($pdf->pdf_path));
        }

        // Store the new PDF file in the public directory
        $pdfFileName = 'pdfs/' . time() . '-' . $request->file('pdf')->getClientOriginalName();
        $request->file('pdf')->move(public_path('pdfs'), $pdfFileName);

        // Update the PDF path in the database
        $pdf->pdf = $pdfFileName;

        // Store the PDF size in bytes (or KB or MB as per your requirement)
        $pdf->size = $pdfSizeInBytes; // Store size in bytes
   
    }

    // Update the PDF record
    $pdf->name = $request->name;
    $pdf->name_ar = $request->name_ar;
    $pdf->save();

    return redirect()->back()->with('success', 'PDF updated successfully!');
}



  // Delete an existing PDF record
  public function destroy($id)
  {
    $permissions = session('permissions');
    if (!isset($permissions['Pdfs']) || !in_array('write', $permissions['Pdfs']['actions'])) {
      abort(403, 'Unauthorized action.');
    }
    // Find the PDF record by ID
    $pdf = Pdf::findOrFail($id);

    // Delete the PDF file and QR code from storage
    Storage::disk('public')->delete($pdf->pdf);
    Storage::disk('public')->delete($pdf->qrcode);

    // Delete the PDF record from the database
    $pdf->delete();

    // Return success message
    return redirect()->back()->with('success', 'PDF deleted successfully!');
  }
}
