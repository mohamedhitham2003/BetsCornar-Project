@extends('layouts.app')

@section('title', __('vaccine_batches.create_title'))
@section('page-title', __('vaccine_batches.create_title'))

@section('content')

@include('vaccine_batches.partials.form', [
    'batch'              => $batch,
    'vaccines'           => $vaccines,
    'action'             => route('vaccine-batches.store'),
    'method'             => 'POST',
    'submitLabel'        => __('vaccine_batches.actions.save'),
    'showRemainingHint'  => true,
])

@endsection