@extends('backend.layouts.app')

@section('admin-content')
    <div class="space-y-6">
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">
            <!-- BREADCRUMBS: $breadcrumbs -->
        </nav>
    </div>

    <x-slot name="breadcrumbsData">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs">
            <x-slot name="title_after">
                @if (request('status'))
                    <span class="badge">{{ ucfirst(request('status')) }}</span>
                @endif
                @if (request('category'))
                    <span class="badge">{{ __('Category: :category', ['category' => request('category')]) }}</span>
                @endif
                @if (request('tag'))
                    <span class="badge">{{ __('Tag: :tag', ['tag' => request('tag')]) }}</span>
                @endif
            </x-slot>
        </x-breadcrumbs>
    </x-slot>

    {!! Hook::applyFilters(PostFilterHook::POSTS_AFTER_BREADCRUMBS, '', $postType) !!}

    @livewire('datatable.post-datatable', ['postType' => $postType ,'lazy' => true])

    {!! Hook::applyFilters(PostFilterHook::POSTS_AFTER_TABLE, '', $postType) !!}
@endsection