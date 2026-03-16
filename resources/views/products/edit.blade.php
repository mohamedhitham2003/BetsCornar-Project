@extends('layouts.app')

@section('title', __('products.edit_title'))
@section('page-title', __('products.edit_title'))

@section('content')

@include('products.partials.form', [
    'product'     => $product,
    'action'      => route('products.update', $product),
    'method'      => 'PUT',
    'submitLabel' => __('products.actions.save'),
])

@endsection
