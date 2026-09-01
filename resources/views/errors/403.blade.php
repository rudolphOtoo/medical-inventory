@extends('errors.layout')

@section('title', __('Access Forbidden'))
@section('code', '403 - FORBIDDEN')

@section('icon')
    <x-ui.icon name="shield" class="size-8 text-rose-400" />
@endsection

@section('heading', __('Restricted Ward Access'))

@section('message')
    {{ __('Your account does not possess the requisite clinical privileges or departmental clearance to access this medical management resource.') }}
@endsection
