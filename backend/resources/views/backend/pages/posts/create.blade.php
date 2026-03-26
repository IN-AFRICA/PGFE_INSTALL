@extends('backend.layouts.app')

@section('admin-content')
    <div class="space-y-6">
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">
            <!-- BREADCRUMBS: $breadcrumbs -->
        </nav>
    </div>

    {!! Hook::applyFilters(PostFilterHook::POSTS_CREATE_AFTER_BREADCRUMBS, '', $postType) !!}

    <form
        action="{{ route('admin.posts.store', $postType) }}"
        method="POST"
        enctype="multipart/form-data"
        data-prevent-unsaved-changes
    >
        @csrf
        @include('backend.pages.posts.partials.form', [
            'post' => null,
            'selectedTerms' => [],
            'postType' => $postType,
            'postTypeModel' => $postTypeModel,
            'taxonomies' => $taxonomies ?? [],
            'parentPosts' => $parentPosts ?? [],
            'mode' => 'create',
        ])
    </form>

    {!! Hook::applyFilters(PostFilterHook::AFTER_POST_FORM, '', $postType) !!}

    @push('scripts')
        {{-- Quill editor removed; add your own init here if needed --}}
    @endpush
@endsection
