@props(['breadcrumbs' => []])

@extends('backend.layouts.app-modules')

@section('title')
    @if ($pageTitle ?? false)
        {{ $pageTitle }}
    @else
        {{ config('app.name') }}
    @endif
@endsection

@section('admin-content')
    <div class="max-w-7xl mx-auto w-full px-2 sm:px-4">
        {{ $slot }}
    </div>
@endsection
