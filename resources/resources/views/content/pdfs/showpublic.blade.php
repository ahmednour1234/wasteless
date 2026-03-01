<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <title>Project Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
            background-color: #007bff;
            color: white;
            padding: 1rem;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .card-body {
            padding: 1.5rem;
        }
        .text-muted {
            font-size: 0.9rem;
        }
        .qr-code img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Company Information -->
        <div class="text-center mb-5">
            <img src="{{ asset($settings->img) }}" alt="Company Logo" class="mb-3 rounded-circle" style="max-width: 120px;">
            <h1 class="text-primary">{{ $settings->name }}</h1>
            <p class="text-muted">Phone: {{ $settings->phone }} | Address: {{ $settings->address }}</p>
        </div>

        <!-- Project Information -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Project Information</div>
                    <div class="card-body text-center">
                        <h2 class="mb-3">{{ $project->name }}</h2>
                        <p class="text-muted">Updated At: {{ $project->updated_at->format('d M, Y h:i A') }}</p>
                        <div class="qr-code my-4">
                            <img src="{{ asset($project['qrcode']) }}" alt="Project QR Code">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDFs Section -->
<div class="row">
    @foreach ($pdfs as $pdf)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">{{ $pdf->name }}</div>
            <div class="card-body text-center">
                <p class="text-muted">{{ $pdf->updated_at->format('d M, Y h:i A') }}</p>
                <!-- QR Code -->
            
                <!-- Embedded PDF Viewer -->
                <div class="mb-3">
                    <iframe 
                        src="{{ asset($pdf['pdf']) }}" 
                        style="width: 100%; height: 200px; border: none;" 
                        title="PDF Preview"></iframe>
                </div>
                <!-- Download Button -->
                <a href="{{ asset( $pdf['pdf']) }}" class="btn btn-primary" target="_blank">
                    View Full PDF
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
