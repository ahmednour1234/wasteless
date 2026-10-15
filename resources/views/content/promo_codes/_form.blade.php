@csrf

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" id="code"
               class="form-control text-uppercase @error('code') is-invalid @enderror"
               value="{{ old('code', $promoCode->code) }}" required>
        @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="type">Discount Type <span class="text-danger">*</span></label>
        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="percentage" {{ old('type', $promoCode->type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
            <option value="fixed" {{ old('type', $promoCode->type) === 'fixed' ? 'selected' : '' }}>Fixed amount (USD)</option>
        </select>
        @error('type') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="value">Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="value" id="value"
               class="form-control @error('value') is-invalid @enderror"
               value="{{ old('value', $promoCode->value) }}" required>
        @error('value') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="max_discount">Max Discount (USD) <small class="text-muted">— for percentage only</small></label>
        <input type="number" step="0.01" min="0" name="max_discount" id="max_discount"
               class="form-control @error('max_discount') is-invalid @enderror"
               value="{{ old('max_discount', $promoCode->max_discount) }}">
        @error('max_discount') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="min_order_amount">Min Order Amount (USD)</label>
        <input type="number" step="0.01" min="0" name="min_order_amount" id="min_order_amount"
               class="form-control @error('min_order_amount') is-invalid @enderror"
               value="{{ old('min_order_amount', $promoCode->min_order_amount) }}">
        @error('min_order_amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="start_date">Start Date</label>
        <input type="datetime-local" name="start_date" id="start_date"
               class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date', optional($promoCode->start_date)->format('Y-m-d\TH:i')) }}">
        @error('start_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="end_date">End Date</label>
        <input type="datetime-local" name="end_date" id="end_date"
               class="form-control @error('end_date') is-invalid @enderror"
               value="{{ old('end_date', optional($promoCode->end_date)->format('Y-m-d\TH:i')) }}">
        @error('end_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="usage_limit">Total Usage Limit <small class="text-muted">— empty = unlimited</small></label>
        <input type="number" min="1" name="usage_limit" id="usage_limit"
               class="form-control @error('usage_limit') is-invalid @enderror"
               value="{{ old('usage_limit', $promoCode->usage_limit) }}">
        @error('usage_limit') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="usage_limit_per_user">Limit Per User <small class="text-muted">— empty = unlimited</small></label>
        <input type="number" min="1" name="usage_limit_per_user" id="usage_limit_per_user"
               class="form-control @error('usage_limit_per_user') is-invalid @enderror"
               value="{{ old('usage_limit_per_user', $promoCode->usage_limit_per_user) }}">
        @error('usage_limit_per_user') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $promoCode->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="mt-4">
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('promo-codes.index') }}" class="btn btn-secondary">Cancel</a>
</div>
