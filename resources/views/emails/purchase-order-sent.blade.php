@extends('emails.layout')
@section('body')
<h2 style="margin:0 0 12px;color:#0d5c3e;">New purchase order {{ $order->reference_number }}</h2>
<p>Dear {{ optional($order->supplier)->contact_person }},</p>
<p>Please find our purchase order below. Kindly confirm and deliver by <strong>{{ optional($order->expected_delivery_date)->format('M d, Y') ?? 'the agreed date' }}</strong>.</p>
@include('emails._items')
<p>{{ $order->terms }}</p>
@if($order->notes)<p style="color:#748279;">Notes: {{ $order->notes }}</p>@endif
<p>Thank you,<br>{{ config('app.name') }}</p>
@endsection
