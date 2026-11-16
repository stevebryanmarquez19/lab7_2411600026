{{-- Shared by create and edit. Expects $order, $suppliers, $products, $action, $method. --}}
@php($cur = config('procurement.currency'))
@php($oldItems = old('items', $order->exists ? $order->items->map(function ($i) { return ['product_id' => $i->product_id, 'quantity' => $i->quantity, 'unit_price' => $i->unit_price]; })->values()->all() : []))
<form action="{{ $action }}" method="POST" id="poForm" novalidate>
  @csrf
  @if($method !== 'POST') @method($method) @endif
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <label for="supplier_id" class="form-label small fw-semibold">Supplier *</label>
      <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
        <option value="">Choose an active supplier</option>
        @foreach($suppliers as $s)
          <option value="{{ $s->id }}" data-lead="{{ $s->lead_time_days }}" data-terms="{{ $s->payment_terms }}" {{ (string) old('supplier_id', $order->supplier_id) === (string) $s->id ? 'selected' : '' }}>{{ $s->company_name }} ({{ $s->lead_time_days }}d lead)</option>
        @endforeach
      </select>
      @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
      <label for="order_date" class="form-label small fw-semibold">Order date *</label>
      <input id="order_date" type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', optional($order->order_date)->toDateString() ?? date('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4">
      <label for="expected_delivery_date" class="form-label small fw-semibold">Expected delivery</label>
      <input id="expected_delivery_date" type="date" name="expected_delivery_date" class="form-control @error('expected_delivery_date') is-invalid @enderror" value="{{ old('expected_delivery_date', optional($order->expected_delivery_date)->toDateString()) }}">
      <div class="form-text">Left blank = order date + supplier lead time.</div>
    </div>
  </div>

  <h2 class="panel-title mt-4">Line items</h2>
  <div class="table-responsive">
    <table class="table align-middle" id="itemsTable">
      <thead><tr><th style="width:42%">Product</th><th style="width:15%">Quantity</th><th style="width:20%">Unit price ({{ $cur }})</th><th class="text-end" style="width:15%">Line total</th><th></th></tr></thead>
      <tbody id="itemsBody"></tbody>
      <tfoot>
        <tr><td colspan="3" class="text-end text-muted">Subtotal</td><td class="text-end" id="subtotalCell">0.00</td><td></td></tr>
        <tr><td colspan="3" class="text-end text-muted">VAT ({{ rtrim(rtrim(number_format(config('procurement.tax_rate') * 100, 2), '0'), '.') }}%)</td><td class="text-end" id="taxCell">0.00</td><td></td></tr>
        <tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold" id="totalCell">0.00</td><td></td></tr>
      </tfoot>
    </table>
  </div>
  <button type="button" class="btn btn-outline-emerald btn-sm" id="addItemBtn"><i class="bi bi-plus-lg me-1"></i>Add line item</button>

  <div class="row g-3 mt-2">
    <div class="col-md-6"><label for="notes" class="form-label small fw-semibold">Notes</label><textarea id="notes" name="notes" rows="2" class="form-control" maxlength="2000">{{ old('notes', $order->notes) }}</textarea></div>
    <div class="col-md-6"><label for="terms" class="form-label small fw-semibold">Terms</label><textarea id="terms" name="terms" rows="2" class="form-control" maxlength="2000">{{ old('terms', $order->terms) }}</textarea></div>
  </div>

  <div class="d-flex gap-2 mt-4 flex-wrap">
    <button type="submit" name="action" value="draft" class="btn btn-outline-emerald"><i class="bi bi-save me-1"></i>Save as draft</button>
    <button type="submit" name="action" value="submit" class="btn btn-emerald"><i class="bi bi-send me-1"></i>Save &amp; submit for approval</button>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-light border">Cancel</a>
  </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const products = @json($products);
  const taxRate = @json((float) config('procurement.tax_rate'));
  const body = document.getElementById('itemsBody');
  const supplier = document.getElementById('supplier_id');
  let index = 0;

  function money(n) { return Number(n || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

  function productOptions(selected) {
    const sid = supplier.value;
    let html = '<option value="">Choose a product</option>';
    products.forEach(p => {
      const mine = sid && String(p.supplier_id) === sid ? ' ★' : '';
      html += `<option value="${p.id}" data-cost="${p.cost_price}" data-min="${p.min_order_quantity}" ${String(p.id) === String(selected) ? 'selected' : ''}>${p.name} (${p.sku}) — ${p.quantity} on hand${mine}</option>`;
    });
    return html;
  }

  function addRow(item) {
    item = item || {};
    const i = index++;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><select name="items[${i}][product_id]" class="form-select form-select-sm product-select" required>${productOptions(item.product_id)}</select></td>
      <td><input type="number" min="1" name="items[${i}][quantity]" class="form-control form-control-sm qty" value="${item.quantity || 1}" required></td>
      <td><input type="number" min="0" step="0.01" name="items[${i}][unit_price]" class="form-control form-control-sm price" value="${item.unit_price !== undefined ? item.unit_price : ''}" required></td>
      <td class="text-end line-total">0.00</td>
      <td class="text-end"><button type="button" class="btn btn-sm action-btn delete-btn remove-row" title="Remove"><i class="bi bi-x-lg"></i></button></td>`;
    body.appendChild(tr);
    recalc();
  }

  function recalc() {
    let subtotal = 0;
    body.querySelectorAll('tr').forEach(tr => {
      const line = (Number(tr.querySelector('.qty').value) || 0) * (Number(tr.querySelector('.price').value) || 0);
      tr.querySelector('.line-total').textContent = money(line);
      subtotal += line;
    });
    const tax = Math.round(subtotal * taxRate * 100) / 100;
    document.getElementById('subtotalCell').textContent = money(subtotal);
    document.getElementById('taxCell').textContent = money(tax);
    document.getElementById('totalCell').textContent = money(subtotal + tax);
  }

  body.addEventListener('change', e => {
    if (e.target.classList.contains('product-select')) {
      const opt = e.target.selectedOptions[0];
      const tr = e.target.closest('tr');
      if (opt && opt.dataset.cost !== undefined) {
        tr.querySelector('.price').value = opt.dataset.cost;           // default to the product's cost price
        const min = Number(opt.dataset.min) || 1;
        if (Number(tr.querySelector('.qty').value) < min) tr.querySelector('.qty').value = min;
      }
    }
    recalc();
  });
  body.addEventListener('input', recalc);
  body.addEventListener('click', e => {
    const btn = e.target.closest('.remove-row');
    if (btn) { btn.closest('tr').remove(); recalc(); }
  });
  document.getElementById('addItemBtn').addEventListener('click', () => addRow());

  // Choosing a supplier fills the expected date from its lead time and highlights its products (★).
  supplier.addEventListener('change', () => {
    const opt = supplier.selectedOptions[0];
    const expected = document.getElementById('expected_delivery_date');
    if (opt && opt.dataset.lead !== undefined && document.getElementById('order_date').value) {
      const d = new Date(document.getElementById('order_date').value);
      d.setDate(d.getDate() + Number(opt.dataset.lead));
      expected.value = d.toISOString().slice(0, 10);
      const terms = document.getElementById('terms');
      if (!terms.value) terms.value = 'Payment terms: ' + opt.dataset.terms;
    }
    body.querySelectorAll('.product-select').forEach(sel => { const v = sel.value; sel.innerHTML = productOptions(v); });
  });

  const existing = @json(array_values($oldItems));
  if (existing.length) existing.forEach(addRow); else addRow();
});
</script>
@endpush
