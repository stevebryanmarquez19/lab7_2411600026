@extends('emails.layout')
@section('body')
<h2 style="margin:0 0 12px;color:#0d5c3e;">Your purchase order was approved ✅</h2>
<p><strong>{{ $order->reference_number }}</strong> ({{ optional($order->supplier)->company_name }}) was approved by {{ optional($order->approvedBy)->name ?? 'a manager' }} on {{ optional($order->approved_at)->format('M d, Y H:i') }}.</p>
@include('emails._items')
<p>The next step is to send the order to the supplier.</p>
<p><a href="{{ route('purchase-orders.show', $order) }}" style="display:inline-block;background:#0d5c3e;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:6px;">Open order</a></p>
@endsection
