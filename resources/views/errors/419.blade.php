@extends('errors.layout')

@section('title', __('Session Expired'))
@section('code', '419 - PAGE EXPIRED')

@section('icon')
    <x-ui.icon name="clock" class="size-8 text-amber-400" />
@endsection

@section('heading', __('Terminal Session Inactive'))

@section('message')
    {{ __('Your workstation security session has expired due to inactivity. Please return to the landing page or refresh your sign-in credentials.') }}
@endsection
