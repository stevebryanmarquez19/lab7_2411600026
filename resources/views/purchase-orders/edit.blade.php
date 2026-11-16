@extends('layouts.app')
@section('title', 'Edit ' . $order->reference_number)
@section('content')
<section class="page-heading mb-4">
  <div class="eyebrow">PROCUREMENT / PURCHASE ORDERS / EDIT</div>
  <h1 class="h3 fw-bold mb-1">Edit {{ $order->reference_number }}</h1>
  <p class="text-muted mb-0">Only draft orders can be edited.</p>
</section>
<div class="panel-card card"><div class="card-body">
  @include('purchase-orders._form', ['action' => route('purchase-orders.update', $order), 'method' => 'PUT'])
</div></div>
@endsection
