@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
<section class="page-heading mb-4">
  <div class="eyebrow">PROCUREMENT / SUPPLIERS / EDIT</div>
  <h1 class="h3 fw-bold mb-1">Edit {{ $supplier->company_name }}</h1>
</section>
<div class="panel-card card"><div class="card-body">
  @include('suppliers._form', ['action' => route('suppliers.update', $supplier), 'method' => 'PUT'])
</div></div>
@endsection
