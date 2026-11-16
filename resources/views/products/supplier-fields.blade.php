{{-- Lab 7: replenishment settings for a product. Included in the add and edit modals ($prefix = add|edit). --}}
<hr class="my-3">
<div class="small fw-semibold text-muted mb-2"><i class="bi bi-truck me-1"></i>SUPPLIER &amp; REPLENISHMENT (optional)</div>
<div class="row g-3">
  <div class="col-12"><label for="{{ $prefix }}_supplier_id" class="form-label small fw-semibold">Supplier</label>
    <select id="{{ $prefix }}_supplier_id" name="supplier_id" class="form-select"><option value="">— None —</option>@foreach(($suppliers ?? []) as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>@endforeach</select></div>
  <div class="col-4"><label for="{{ $prefix }}_cost_price" class="form-label small fw-semibold">Cost price</label><input id="{{ $prefix }}_cost_price" name="cost_price" type="number" step="0.01" min="0" class="form-control" placeholder="0.00"></div>
  <div class="col-4"><label for="{{ $prefix }}_pack_size" class="form-label small fw-semibold">Pack size</label><input id="{{ $prefix }}_pack_size" name="pack_size" type="number" min="1" class="form-control" placeholder="1"></div>
  <div class="col-4"><label for="{{ $prefix }}_min_order_quantity" class="form-label small fw-semibold">Min. order qty</label><input id="{{ $prefix }}_min_order_quantity" name="min_order_quantity" type="number" min="1" class="form-control" placeholder="1"></div>
</div>
