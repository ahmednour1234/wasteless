@extends('layouts/layoutMaster')

@section('title', 'Takaml Pdfs - Management')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}"> <!-- Custom styles for the page -->
@endsection

@section('page-script')
  <script src="{{ asset('assets/js/app-user-list.js') }}"></script>
@endsection

@section('content')
<!-- Button to Open Add Offer Modal -->
<h1>{{ $project->name }}</h1>
<button class="btn btn-primary btn-lg w-100 my-3" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddOffer">
  <i class="ti ti-plus me-2"></i><span>@lang('Add New PDF')</span>
</button>

<!-- PDF Table -->
<div class="card">
  <div class="card-header">
    <h5 class="card-title">@lang('PDF Management')</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>@lang('Name')</th>
          <th>@lang('Description')</th>
          <th>@lang('Size')</th>
                    <th>@lang('pdf')</th>

          <th>@lang('Actions')</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($pdfs as $pdf)
          <tr>
            <td>{{ $pdf->name }}</td>
            <td>{{ $pdf->pdf }}</td>
            <td>{{ number_format($pdf->size / 1024 / 1024, 2) }} MB</td> <!-- Display size in MB -->
        
            <td>
  @if ($pdf->pdf)
    <a href="{{ asset($pdf->pdf) }}" target="_blank" class="btn btn-primary">
      @lang('View PDF')
    </a>
  @else
    <span class="text-muted">@lang('No PDF Available')</span>
  @endif
</td>

            <td>
              <div class="dropdown">
                <button class="btn p-0 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ti ti-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditOffer{{ $pdf->id }}">
                      <i class="ti ti-pencil me-1"></i> @lang('Edit')
                    </a></li>
                  <li>
                    <form method="POST" action="{{ route('dashboard-pdf.destroy', $pdf->id) }}" style="display: inline;">
                      @csrf
                      @method('DELETE')
                      <button class="dropdown-item text-danger" type="submit">
                        <i class="ti ti-trash me-1"></i> @lang('Delete')
                      </button>
                    </form>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Offcanvas for Add PDF -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddOffer" aria-labelledby="offcanvasAddOfferLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasAddOfferLabel" class="offcanvas-title">@lang('Add New PDF')</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
   <form method="POST" action="{{ route('dashboard-pdf.store') }}" enctype="multipart/form-data">
  @csrf
  <div class="mb-3">
    <label class="form-label" for="pdf-files">@lang('Upload PDFs')</label>
    <input 
      type="file" 
      class="form-control" 
      id="pdf-files" 
      name="pdfs[]" 
      accept=".pdf" 
      multiple 
      required 
    />
    <small class="text-muted">
      @lang('You can upload multiple PDF files. Maximum file size per PDF is 300MB.')
    </small>
  </div>
  <input type="hidden" name="project_id" value="{{ $project->id }}" />
  <div class="d-flex justify-content-between">
    <button type="submit" class="btn btn-primary">@lang('Submit')</button>
    <button type="reset" class="btn btn-secondary">@lang('Cancel')</button>
  </div>
</form>

  </div>
</div>

<!-- Offcanvas for Edit PDF -->
@foreach($pdfs as $pdf)
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditOffer{{ $pdf->id }}" aria-labelledby="offcanvasEditOfferLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasEditOfferLabel" class="offcanvas-title">@lang('Edit PDF')</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <form method="POST" action="{{ route('dashboard-pdf.update', $pdf->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label class="form-label" for="offer-name">@lang('PDF Name')</label>
          <input type="text" class="form-control" id="offer-name" name="name" value="{{ old('name', $pdf->name) }}" required />
        </div>
        <!--<div class="mb-3">-->
        <!--  <label class="form-label" for="offer-name-ar">@lang('PDF Name (Arabic)')</label>-->
        <!--  <input type="text" class="form-control" id="offer-name-ar" name="name_ar" value="{{ old('name_ar', $pdf->name_ar) }}" required />-->
        <!--</div>-->
        <div class="mb-3">
          <label class="form-label" for="pdf-file">@lang('Upload New PDF (Optional)')</label>
          <input type="file" class="form-control" id="pdf-file" name="pdf" accept=".pdf" />
        </div>
        @if($pdf->pdf)
          <div class="mb-3">
            <label class="form-label">@lang('Current PDF')</label>
            <a href="{{ asset($pdf->pdf) }}" target="_blank" class="btn btn-link">@lang('View Current PDF')</a>
          </div>
        @endif
        <button type="submit" class="btn btn-primary">@lang('Submit')</button>
        <button type="reset" class="btn btn-secondary" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
      </form>
    </div>
  </div>
@endforeach
@endsection
