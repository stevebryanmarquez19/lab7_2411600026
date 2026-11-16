@extends('layouts.app')
@section('title', 'Add Supplier')
@section('content')
<section class="page-heading mb-4">
  <div class="eyebrow">PROCUREMENT / SUPPLIERS / NEW</div>
  <h1 class="h3 fw-bold mb-1">Add Supplier</h1>
  <p class="text-muted mb-0">Fields marked * are required.</p>
</section>
<div class="panel-card card"><div class="card-body">
  @include('suppliers._form', ['action' => route('suppliers.store'), 'method' => 'POST'])
</div></div>
@endsection
