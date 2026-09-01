@extends('errors.layout')

@section('title', __('Service Unavailable'))
@section('code', '503 - MAINTENANCE')

@section('icon')
    <x-ui.icon name="wrench" class="size-8 text-amber-400" />
@endsection

@section('heading', __('LAN Node Maintenance Window'))

@section('message')
    {{ __('MedTrack server node is currently undergoing scheduled database maintenance or backup synchronization. Operational access will resume shortly.') }}
@endsection
