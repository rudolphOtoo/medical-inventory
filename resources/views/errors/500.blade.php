@extends('errors.layout')

@section('title', __('Internal Server Error'))
@section('code', '500 - SERVER ERROR')

@section('icon')
    <x-ui.icon name="exclamation" class="size-8 text-rose-400" />
@endsection

@section('heading', __('LAN Gateway Node Fault'))

@section('message')
    {{ __('An unexpected internal exception occurred while processing this clinical operation. The incident has been logged to the central server diagnostics.') }}
@endsection
