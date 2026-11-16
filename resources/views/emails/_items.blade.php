<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:16px 0;font-size:13px;">
  <tr style="background:#0d5c3e;color:#ffffff;"><th align="left" style="padding:8px;">Product</th><th align="right" style="padding:8px;">Qty</th><th align="right" style="padding:8px;">Unit price</th><th align="right" style="padding:8px;">Line total</th></tr>
  @foreach($order->items as $item)
  <tr><td style="padding:8px;border-bottom:1px solid #e7eee9;">{{ $item->product_name }} <span style="color:#748279;">{{ $item->sku }}</span></td><td align="right" style="padding:8px;border-bottom:1px solid #e7eee9;">{{ $item->quantity }}</td><td align="right" style="padding:8px;border-bottom:1px solid #e7eee9;">{{ config('procurement.currency') }}{{ number_format($item->unit_price, 2) }}</td><td align="right" style="padding:8px;border-bottom:1px solid #e7eee9;">{{ config('procurement.currency') }}{{ number_format($item->line_total, 2) }}</td></tr>
  @endforeach
  <tr><td colspan="3" align="right" style="padding:8px;"><strong>Total (incl. VAT)</strong></td><td align="right" style="padding:8px;"><strong>{{ config('procurement.currency') }}{{ number_format($order->total, 2) }}</strong></td></tr>
</table>
