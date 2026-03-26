@extends('backend.layouts.app')

@section('admin-content')
    <div class="space-y-6">
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">
            <!-- BREADCRUMBS: $breadcrumbs -->
        </nav>
    </div>

    {!! Hook::applyFilters(UserFilterHook::PROFILE_AFTER_BREADCRUMBS, '') !!}

    <x-card>
        <form
            action="{{ route('profile.update') }}"
            method="POST"
            class="space-y-6"
            enctype="multipart/form-data"
            data-prevent-unsaved-changes
        >
            @csrf
            @method('PUT')

            @include('backend.pages.users.partials.form', [
                'user' => $user,
                'roles' => [],
                'timezones' => $timezones ?? [],
                'locales' => $locales ?? [],
                'userMeta' => $userMeta ?? [],
                'mode' => 'profile',
                'showUsername' => true,
                'showRoles' => false,
                'showAdditional' => true,
                'cancelUrl' => route('admin.dashboard')
            ])
        </form>
    </x-card>

    {!! Hook::applyFilters(UserFilterHook::PROFILE_AFTER_FORM, '') !!}
@endsection
