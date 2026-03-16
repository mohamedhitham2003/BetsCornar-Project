@extends('layouts.app')

@section('title', __('products.create_title'))
@section('page-title', __('products.create_title'))

@section('content')

@include('products.partials.form', [
    'product'     => $product,
    'action'      => route('products.store'),
    'method'      => 'POST',
    'submitLabel' => __('products.actions.save'),
])

@endsection
