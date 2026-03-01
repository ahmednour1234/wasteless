@extends('layouts/layoutMaster')

@section('title', 'North Sea')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
@endsection

@section('page-script')
  <script src="{{ asset('assets/js/app-user-list.js') }}"></script>
@endsection

@section('content')
<!-- Button to Open Add Offer Modal -->
<button style="width: 100%; margin: 20px 0;" class="add-new btn btn-primary waves-effect waves-light" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddOffer">
    <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">@lang('Add New Ellithy Offer')</span>
</button>

<!-- Offers Table -->
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>@lang('Name')</th>
                    <th>@lang('Description')</th>
                    <th>@lang('QRCODE')</th>
                    <th>@lang('Copy Link')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($projects as $project)
                    <tr>
                        <td><a href="{{ route('dashboard-project.show', $project->id) }}">{{ $project->name }}</a></td>
                        <td>{{ $project->title }}</td>
                        <td>@if ($project->qrcode)
<img src="{{ url($project->qrcode) }}" style="width:30%;height:30%;" alt="Project QR Code">
                      @endif
                      </td>
                     <td>
  @if ($project->qrcode)
    <a href="javascript:void(0);" onclick="copyToClipboard('{{ url("/show/project/showpublic/$project->id") }}')" >
      Copy QR Code Link
    </a>
  @endif
</td>



                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <button class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEditOffer{{ $project->id }}">
                                        <i class="ti ti-pencil me-1"></i> @lang('Edit')
                                    </button>

                                    <form method="POST" action="{{ route('dashboard-project.destroy', $project->id) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="ti ti-trash me-1"></i> @lang('Delete')
                                        </button>
                                    </form>
                                    <form method="Get" action="{{ route('dashboard-project.show', $project->id) }}" style="display: inline;">
                                      @csrf
                                      <button class="dropdown-item text-danger" type="submit">
                                          <i class="ti ti-show me-1"></i> @lang('Show')
                                      </button>
                                  </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Offcanvas for Add Offer -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddOffer" aria-labelledby="offcanvasAddOfferLabel">
    <div class="offcanvas-header">
        <h5 id="offcanvasAddOfferLabel" class="offcanvas-title">@lang('Add New Ellithy Offer')</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="@lang('Close')"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
        <form method="POST" action="{{ route('dashboard-project.store') }}" id="offerForm">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="offer-name">@lang('Offer Name')</label>
                <input type="text" class="form-control" id="offer-name" name="name" placeholder="@lang('Offer Name')" required />
            </div>
            <!--<div class="mb-3">-->
            <!--    <label class="form-label" for="offer-name-ar">@lang('Offer Name (Arabic)')</label>-->
            <!--    <input type="text" class="form-control" id="offer-name-ar" name="name_ar" placeholder="@lang('Offer Name (Arabic)')" required />-->
            <!--</div>-->
            <div class="mb-3">
                <label class="form-label" for="offer-desc">@lang('Offer Description')</label>
                <textarea class="form-control" id="offer-desc" name="title" placeholder="@lang('Offer Description')" required></textarea>
            </div>
            <!--<div class="mb-3">-->
            <!--    <label class="form-label" for="offer-desc-ar">@lang('Offer Description (Arabic)')</label>-->
            <!--    <textarea class="form-control" id="offer-desc-ar" name="title_ar" placeholder="@lang('Offer Description (Arabic)')" required></textarea>-->
            <!--</div>-->

            <button type="submit" class="btn btn-primary me-sm-3 me-1">@lang('Submit')</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
        </form>
    </div>
</div>

<!-- Modal for Edit Offer -->
@foreach($projects as $project)
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditOffer{{ $project->id }}" aria-labelledby="offcanvasEditOfferLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasEditOfferLabel" class="offcanvas-title">@lang('Edit Ellithy Offer')</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="@lang('Close')"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
            <form method="POST" action="{{ route('dashboard-project.update', $project->id) }}" id="offerForm">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="offer-name">@lang('Offer Name')</label>
                    <input type="text" class="form-control" id="offer-name" name="name" placeholder="@lang('Offer Name')" value="{{ old('name', $project->name) }}" required />
                </div>
                <!--<div class="mb-3">-->
                <!--    <label class="form-label" for="offer-name-ar">@lang('Offer Name (Arabic)')</label>-->
                <!--    <input type="text" class="form-control" id="offer-name-ar" name="name_ar" placeholder="@lang('Offer Name (Arabic)')" value="{{ old('name_ar', $project->name_ar) }}" required />-->
                <!--</div>-->
                <div class="mb-3">
                    <label class="form-label" for="offer-desc">@lang('Offer Description')</label>
                    <textarea class="form-control" id="offer-desc" name="title" placeholder="@lang('Offer Description')" required>{{ old('title', $project->title) }}</textarea>
                </div>
                <!--<div class="mb-3">-->
                <!--    <label class="form-label" for="offer-desc-ar">@lang('Offer Description (Arabic)')</label>-->
                <!--    <textarea class="form-control" id="offer-desc-ar" name="title_ar" placeholder="@lang('Offer Description (Arabic)')" required>{{ old('title_ar', $project->title_ar) }}</textarea>-->
                <!--</div>-->
                <!--<div class="mb-3">-->
                <!--    <label class="form-label" for="offer-type">@lang('Offer Type')</label>-->
                <!--    <select class="form-select" id="offer-type" name="type" required>-->
                <!--        <option value="1">@lang('Seasonal')</option>-->
                <!--        <option value="0">@lang('Non-Seasonal')</option>-->
                <!--    </select>-->
                <!--</div>-->
                <button type="submit" class="btn btn-primary me-sm-3 me-1">@lang('Submit')</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
            </form>
        </div>
    </div>
@endforeach


@endsection
<script>
  function copyToClipboard(text) {
    // Create a temporary input element
    var tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    
    // Select and copy the text
    tempInput.select();
    document.execCommand('copy');
    
    // Remove the temporary input element
    document.body.removeChild(tempInput);
    
    // Optionally alert the user
    alert('QR code link copied to clipboard!');
  }
</script>