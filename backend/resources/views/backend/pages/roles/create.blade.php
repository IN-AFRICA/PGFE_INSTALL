@extends('backend.layouts.app')

@section('admin-content')

    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Rôles', 'url' => route('admin.roles.index')]]" current="Créer un Rôle" />

    {!! Hook::ApplyFilters(RoleFilterHook::ROLE_CREATE_BEFORE_FORM, '') !!}

    <div class="max-w-2xl mx-auto bg-white border border-zinc-200 rounded-xl shadow-sm p-8 mt-8">
        <form action="{{ route('admin.roles.store') }}" method="POST" data-prevent-unsaved-changes class="space-y-8">
            @csrf
            <div class="space-y-4">
                <label for="name" class="text-[11px] font-black uppercase tracking-widest text-zinc-500">Nom du Rôle</label>
                <input required autofocus name="name" value="{{ old('name') ?? '' }}" type="text"
                    placeholder="ex: secretaire-ecole"
                    class="h-10 rounded-md border-zinc-200 bg-white px-3 text-sm placeholder:text-zinc-400 focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 transition-all w-full" />
            </div>

            @include('backend.pages.roles.partials.form', ['role' => null])

            <div class="flex justify-end">
                <button type="submit" class="h-9 px-6 bg-zinc-900 text-zinc-50 rounded-md text-xs font-bold uppercase tracking-widest hover:bg-black transition-all flex items-center gap-2">
                    <iconify-icon icon="lucide:plus-circle" width="16"></iconify-icon>
                    Créer le rôle
                </button>
            </div>
        </form>
    </div>

    {!! Hook::ApplyFilters(RoleFilterHook::ROLE_CREATE_AFTER_FORM, '') !!}
@endsection