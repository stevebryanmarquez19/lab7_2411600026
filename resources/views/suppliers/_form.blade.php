{{-- Shared by suppliers.create and suppliers.edit. Expects $supplier, $action, $method. --}}
<form action="{{ $action }}" method="POST" novalidate>
  @csrf
  @if($method !== 'POST') @method($method) @endif
  <div class="row g-3">
    <div class="col-md-6">
      <label for="company_name" class="form-label small fw-semibold">Company name *</label>
      <input id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $supplier->company_name) }}" maxlength="255" required>
      @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
      <label for="contact_person" class="form-label small fw-semibold">Contact person *</label>
      <input id="contact_person" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $supplier->contact_person) }}" maxlength="255" required>
      @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
      <label for="email" class="form-label small fw-semibold">E-mail *</label>
      <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supplier->email) }}" maxlength="255" required>
      @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
      <label for="phone" class="form-label small fw-semibold">Phone *</label>
      <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $supplier->phone) }}" maxlength="30" placeholder="+63 82 221 4455" required>
      @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
      <label for="address" class="form-label small fw-semibold">Address *</label>
      <textarea id="address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror" maxlength="1000" required>{{ old('address', $supplier->address) }}</textarea>
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
      <label for="payment_terms" class="form-label small fw-semibold">Payment terms *</label>
      <select id="payment_terms" name="payment_terms" class="form-select @error('payment_terms') is-invalid @enderror" required>
        @foreach(\App\Models\Supplier::PAYMENT_TERMS as $term)
          <option value="{{ $term }}" {{ old('payment_terms', $supplier->payment_terms) === $term ? 'selected' : '' }}>{{ $term }}</option>
        @endforeach
      </select>
      @error('payment_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
      <label for="lead_time_days" class="form-label small fw-semibold">Lead time (days) *</label>
      <input id="lead_time_days" type="number" min="0" max="365" name="lead_time_days" class="form-control @error('lead_time_days') is-invalid @enderror" value="{{ old('lead_time_days', $supplier->lead_time_days) }}" required>
      @error('lead_time_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
      <label for="minimum_order_amount" class="form-label small fw-semibold">Minimum order amount *</label>
      <div class="input-group"><span class="input-group-text">{{ config('procurement.currency') }}</span>
        <input id="minimum_order_amount" type="number" step="0.01" min="0" name="minimum_order_amount" class="form-control @error('minimum_order_amount') is-invalid @enderror" value="{{ old('minimum_order_amount', $supplier->minimum_order_amount ?? 0) }}" required>
        @error('minimum_order_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-12">
      <label for="notes" class="form-label small fw-semibold">Notes</label>
      <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror" maxlength="2000">{{ old('notes', $supplier->notes) }}</textarea>
      @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
      <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Active (inactive suppliers cannot receive new purchase orders)</label>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-emerald"><i class="bi bi-check2 me-1"></i> Save supplier</button>
    <a href="{{ route('suppliers.index') }}" class="btn btn-light border">Cancel</a>
  </div>
</form>
