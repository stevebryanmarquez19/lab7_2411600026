<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>{{ $order->reference_number }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a2e24; }
  h1 { color: #0d5c3e; font-size: 20px; margin: 0 0 4px; }
  .muted { color: #748279; }
  table { width: 100%; border-collapse: collapse; margin-top: 12px; }
  th { background: #0d5c3e; color: #fff; text-align: left; padding: 6px; }
  td { padding: 6px; border-bottom: 1px solid #e7eee9; }
  .r { text-align: right; }
  .box { width: 48%; display: inline-block; vertical-align: top; }
</style></head><body>
@php($cur = config('procurement.currency'))
<h1>Purchase Order {{ $order->reference_number }}</h1>
<div class="muted">{{ config('app.name') }} · Status: {{ $order->status_label }}</div>
<p>
  <span class="box"><strong>Supplier</strong><br>{{ optional($order->supplier)->company_name }}<br>{{ optional($order->supplier)->contact_person }}<br>{{ optional($order->supplier)->email }}<br>{{ optional($order->supplier)->address }}</span>
  <span class="box"><strong>Order date:</strong> {{ $order->order_date->format('M d, Y') }}<br><strong>Expected delivery:</strong> {{ optional($order->expected_delivery_date)->format('M d, Y') ?? '—' }}<br><strong>Terms:</strong> {{ $order->terms ?: '—' }}</span>
</p>
<table>
  <thead><tr><th>SKU</th><th>Product</th><th class="r">Qty</th><th class="r">Unit price</th><th class="r">Line total</th></tr></thead>
  <tbody>
  @foreach($order->items as $item)
    <tr><td>{{ $item->sku }}</td><td>{{ $item->product_name }}</td><td class="r">{{ $item->quantity }}</td><td class="r">{{ $cur }}{{ number_format($item->unit_price, 2) }}</td><td class="r">{{ $cur }}{{ number_format($item->line_total, 2) }}</td></tr>
  @endforeach
  </tbody>
  <tfoot>
    <tr><td colspan="4" class="r">Subtotal</td><td class="r">{{ $cur }}{{ number_format($order->subtotal, 2) }}</td></tr>
    <tr><td colspan="4" class="r">VAT</td><td class="r">{{ $cur }}{{ number_format($order->tax_amount, 2) }}</td></tr>
    <tr><td colspan="4" class="r"><strong>Total</strong></td><td class="r"><strong>{{ $cur }}{{ number_format($order->total, 2) }}</strong></td></tr>
  </tfoot>
</table>
@if($order->notes)<p><strong>Notes:</strong> {{ $order->notes }}</p>@endif
</body></html>
