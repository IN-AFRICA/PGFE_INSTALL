@extends('backend.layouts.app')

@section('admin-content')
    <div class="space-y-6">
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">
            <!-- BREADCRUMBS: $breadcrumbs -->
        </nav>
    </div>

    {!! Hook::applyFilters(TermFilterHook::TERM_AFTER_BREADCRUMBS, '', $taxonomyModel) !!}

    <div class="max-w-4xl mx-auto">
        @include('backend.pages.terms.partials.form')
    </div>

    @push('scripts')
        {{-- Quill editor removed; add your own init here if needed --}}
    @endpush
@endsection
