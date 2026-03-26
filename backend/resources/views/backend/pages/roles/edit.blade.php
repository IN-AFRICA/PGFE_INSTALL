@extends('backend.layouts.app')

@section('admin-content')

    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Rôles', 'url' => route('admin.roles.index')]]" current="Éditer un Rôle" />

    {!! Hook::ApplyFilters(RoleFilterHook::ROLE_EDIT_BEFORE_FORM, '') !!}

    <form
        action="{{ route('admin.roles.update', $role->id) }}"
        method="POST"
        data-prevent-unsaved-changes
    >
        @csrf
        @method('PUT')
        @include('backend.pages.roles.partials.form', ['role' => $role])
    </form>

    {!! Hook::ApplyFilters(RoleFilterHook::ROLE_EDIT_AFTER_FORM, '') !!}
@endsection
