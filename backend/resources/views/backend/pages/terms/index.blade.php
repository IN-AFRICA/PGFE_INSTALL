@extends('backend.layouts.app')

@section('admin-content')
    <div class="space-y-6">
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">
            <!-- BREADCRUMBS: $breadcrumbs -->
        </nav>
    </div>

    {!! Hook::applyFilters(TermFilterHook::TERM_AFTER_BREADCRUMBS, '', $taxonomyModel) !!}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            @include('backend.pages.terms.partials.form')
        </div>

        <div class="lg:col-span-2 space-y-6">
            @livewire('datatable.term-datatable', ['taxonomy' => $taxonomy])
        </div>
    </div>
@endsection