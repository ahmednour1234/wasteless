{{-- resources/views/content/bundles/create.blade.php --}}

@extends('layouts/layoutMaster')

@section('title', 'Create Bundle')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
@endsection

@section('page-style')
<!-- أي CSS إضافي إذا لزم الأمر -->
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // عند تغيير الشركة، نُرشّح قائمة الفروع التي تنتمي لتلك الشركة فقط
        const companySelect = document.getElementById('company_id');
        const branchSelect  = document.getElementById('branch_id');
        const allBranches   = @json($branches);

        function filterBranches() {
            const selectedCompany = companySelect.value;
            branchSelect.innerHTML = '<option value="">-- Select Branch --</option>';
            if (!selectedCompany) return;

            allBranches
                .filter(b => b.company_id == selectedCompany)
                .forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.id;
                    opt.text  = b.name;
                    branchSelect.appendChild(opt);
                });
        }

        companySelect.addEventListener('change', filterBranches);

        // تهيئة أولية إذا كانت قيمة قديمة موجودة
        @if(old('company_id'))
            filterBranches();
            @if(old('branch_id'))
                branchSelect.value = "{{ old('branch_id') }}";
            @endif
        @endif
    });
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Create New Bundle</h5>
    </div>

    <div class="card-body">
      <form action="{{ route('bundels.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- 1) Name --}}
        <div class="mb-3">
          <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
          <input 
            type="text" 
            id="name" 
            name="name" 
            class="form-control @error('name') is-invalid @enderror" 
            value="{{ old('name') }}" 
            required
          >
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- 2) Description --}}
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea 
            id="description" 
            name="description" 
            rows="3"
            class="form-control @error('description') is-invalid @enderror"
          >{{ old('description') }}</textarea>
          @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- 3) Company --}}
        <div class="mb-3">
          <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
          <select 
            id="company_id" 
            name="company_id" 
            class="form-select @error('company_id') is-invalid @enderror" 
            required
          >
            <option value="">-- Select Company --</option>
            @foreach($companies as $company)
              <option 
                value="{{ $company->id }}" 
                {{ old('company_id') == $company->id ? 'selected' : '' }}
              >
                {{ $company->name }}
              </option>
            @endforeach
          </select>
          @error('company_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- 4) Category --}}
        <div class="mb-3">
          <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
          <select 
            id="category_id" 
            name="category_id" 
            class="form-select @error('category_id') is-invalid @enderror" 
            required
          >
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
              <option 
                value="{{ $category->id }}" 
                {{ old('category_id') == $category->id ? 'selected' : '' }}
              >
                {{ $category->name }}
              </option>
            @endforeach
          </select>
          @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- 5) Branch (filtered by company) --}}
        <div class="mb-3">
          <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
          <select 
            id="branch_id" 
            name="branch_id" 
            class="form-select @error('branch_id') is-invalid @enderror" 
            required
          >
            <option value="">-- Select Branch --</option>
            {{-- ستُملأ ديناميكيًا بواسطة JavaScript --}}
          </select>
          @error('branch_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- 6) Price --}}
        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
            <input 
              type="number" 
              step="0.01" 
              id="price" 
              name="price" 
              class="form-control @error('price') is-invalid @enderror" 
              value="{{ old('price') }}" 
              required
            >
            @error('price')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-4 mb-3">
            <label for="price_after_discount" class="form-label">Price After Discount</label>
            <input 
              type="number" 
              step="0.01" 
              id="price_after_discount" 
              name="price_after_discount" 
              class="form-control @error('price_after_discount') is-invalid @enderror" 
              value="{{ old('price_after_discount') }}"
            >
            @error('price_after_discount')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-4 mb-3">
            <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
            <input 
              type="number" 
              id="stock" 
              name="stock" 
              class="form-control @error('stock') is-invalid @enderror" 
              value="{{ old('stock') }}" 
              required
            >
            @error('stock')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- 7) Opening and Ended Time --}}
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="opening_time" class="form-label">Opening Time <span class="text-danger">*</span></label>
            <input 
              type="datetime-local" 
              id="opening_time" 
              name="opening_time" 
              class="form-control @error('opening_time') is-invalid @enderror" 
              value="{{ old('opening_time') }}" 
              required
            >
            @error('opening_time')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="ended_time" class="form-label">Ended Time <span class="text-danger">*</span></label>
            <input 
              type="datetime-local" 
              id="ended_time" 
              name="ended_time" 
              class="form-control @error('ended_time') is-invalid @enderror" 
              value="{{ old('ended_time') }}" 
              required
            >
            @error('ended_time')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- 8) Active --}}
        <div class="form-check form-switch mb-3">
          <input 
            class="form-check-input @error('active') is-invalid @enderror" 
            type="checkbox" 
            id="active" 
            name="active" 
            value="1" 
            {{ old('active', true) ? 'checked' : '' }}
          >
          <label class="form-check-label" for="active">Active</label>
          @error('active')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- 9) Upload Image --}}
        <div class="mb-3">
          <label for="image" class="form-label">Upload Image</label>
          <input 
            class="form-control @error('image') is-invalid @enderror" 
            type="file" 
            id="image" 
            name="image" 
            accept="image/*"
          >
          @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Submit --}}
        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save me-1"></i> Save Bundle
          </button>
          <a href="{{ route('bundels.index') }}" class="btn btn-outline-secondary ms-2">
            Cancel
          </a>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection
