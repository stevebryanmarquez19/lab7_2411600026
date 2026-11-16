@extends('emails.layout')
@section('body')
<h2 style="margin:0 0 12px;color:#0d5c3e;">Purchase order awaiting approval</h2>
<p><strong>{{ $order->reference_number }}</strong> for <strong>{{ optional($order->supplier)->company_name }}</strong> was submitted by {{ optional($order->createdBy)->name ?? 'the system' }} and needs a decision.</p>
@if($order->requiresAdminApproval())<p style="background:#fff7e0;border-left:4px solid #f3a712;padding:8px 12px;">This order is {{ config('procurement.currency') }}{{ number_format(config('procurement.admin_approval_threshold')) }} or more, so an <strong>administrator</strong> must approve it.</p>@endif
@include('emails._items')
<p>Expected delivery: {{ optional($order->expected_delivery_date)->format('M d, Y') ?? 'not set' }}</p>
<p><a href="{{ route('purchase-orders.show', $order) }}" style="display:inline-block;background:#0d5c3e;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:6px;">Review order</a></p>
@endsection
