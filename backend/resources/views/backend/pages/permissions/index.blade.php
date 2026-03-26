@extends('backend.layouts.app')

@section('admin-content')
    <div class="space-y-6">
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">
            <!-- BREADCRUMBS: $breadcrumbs -->
        </nav>
    </div>

    @livewire('datatable.permission-datatable', ['lazy' => true])
@endsection
