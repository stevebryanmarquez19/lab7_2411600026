@extends('emails.layout')
@section('body')
<h2 style="margin:0 0 12px;color:#0d5c3e;">Daily reorder check</h2>
<p>{{ $products->count() }} product(s) reached their reorder point. {{ $orders->count() }} draft purchase order(s) were created and are waiting to be reviewed.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:16px 0;font-size:13px;">
  <tr style="background:#0d5c3e;color:#ffffff;"><th align="left" style="padding:8px;">Product</th><th align="right" style="padding:8px;">On hand</th><th align="left" style="padding:8px;">Supplier</th></tr>
  @foreach($products as $p)
  <tr><td style="padding:8px;border-bottom:1px solid #e7eee9;">{{ $p->name }} <span style="color:#748279;">{{ $p->sku }}</span></td><td align="right" style="padding:8px;border-bottom:1px solid #e7eee9;">{{ $p->quantity }}</td><td style="padding:8px;border-bottom:1px solid #e7eee9;">{{ optional($p->supplier)->company_name }}</td></tr>
  @endforeach
</table>
<p><strong>Draft orders</strong></p>
<ul>
@foreach($orders as $o)
  <li><a href="{{ route('purchase-orders.show', $o) }}">{{ $o->reference_number }}</a> — {{ $o->supplier->company_name }} — {{ config('procurement.currency') }}{{ number_format($o->total, 2) }}</li>
@endforeach
</ul>
@endsection
