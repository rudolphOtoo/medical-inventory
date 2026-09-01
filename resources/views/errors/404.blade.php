@extends('errors.layout')

@section('title', __('Page Not Found'))
@section('code', '404 - NOT FOUND')

@section('icon')
    <x-ui.icon name="search" class="size-8 text-rose-400" />
@endsection

@section('heading', __('Medical Resource Not Located'))

@section('message')
    {{ __('The medical device record, department directory, or terminal page you requested does not exist on this hospital LAN node or has been archived.') }}
@endsection
