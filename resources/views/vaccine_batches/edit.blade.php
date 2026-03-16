@extends('layouts.app')

@section('title', __('vaccine_batches.edit_title'))
@section('page-title', __('vaccine_batches.edit_title'))

@section('content')

@include('vaccine_batches.partials.form', [
    'batch'             => $batch,
    'vaccines'          => $vaccines,
    'action'            => route('vaccine-batches.update', $batch),
    'method'            => 'PUT',
    'submitLabel'       => __('vaccine_batches.actions.save'),
    'showRemainingHint' => false,
])

@endsection