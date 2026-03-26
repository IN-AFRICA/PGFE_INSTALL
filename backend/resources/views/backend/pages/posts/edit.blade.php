@extends('backend.layouts.app')

@section('admin-content')
    <div class="space-y-6">
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">
            <!-- BREADCRUMBS: $breadcrumbs -->
        </nav>
    </div>

    {!! Hook::applyFilters(PostFilterHook::POSTS_EDIT_AFTER_BREADCRUMBS, '', $postType) !!}

    <x-card>
        <form
            action="{{ route('admin.posts.update', [$postType, $post->id]) }}"
            method="POST"
            class="space-y-6"
            enctype="multipart/form-data"
            data-prevent-unsaved-changes
        >
            @csrf
            @method('PUT')

            @include('backend.pages.posts.partials.form', [
                'post' => $post,
                'selectedTerms' => $selectedTerms ?? [],
                'postType' => $postType,
                'postTypeModel' => $postTypeModel,
                'taxonomies' => $taxonomies ?? [],
                'parentPosts' => $parentPosts ?? [],
                'mode' => 'edit',
            ])
        </form>
    </x-card>

    {!! Hook::applyFilters(PostFilterHook::AFTER_POST_FORM, '', $postType) !!}

    @push('scripts')
        {{-- Quill editor removed; add your own init here if needed --}}
    @endpush
@endsection
