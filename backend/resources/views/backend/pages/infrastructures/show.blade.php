@extends('backend.layouts.app')
@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Infrastructures', 'url' => route('admin.infrastructures.index')]]" current="Détail de l'infrastructure" />
    <div class="max-w-xl mx-7">
        <h1 class="text-lg font-semibold mb-4">Détail de l'infrastructure</h1>
        <div class="mb-3">
            <strong>Nom :</strong> {{ $infrastructure->name }}
        </div>
        <a href="{{ route('admin.infrastructures.edit', $infrastructure->id) }}" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 font-medium text-sm shadow">Modifier</a>
    </div>
@endsection
