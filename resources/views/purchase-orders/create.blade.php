@extends('layouts.app')
@section('title', 'New Purchase Order')
@section('content')
<section class="page-heading mb-4">
  <div class="eyebrow">PROCUREMENT / PURCHASE ORDERS / NEW</div>
  <h1 class="h3 fw-bold mb-1">New Purchase Order</h1>
  <p class="text-muted mb-0">The reference number is generated automatically when you save.</p>
</section>
<div class="panel-card card"><div class="card-body">
  @include('purchase-orders._form', ['action' => route('purchase-orders.store'), 'method' => 'POST'])
</div></div>
@endsection
