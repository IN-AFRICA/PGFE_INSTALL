@extends('backend.layouts.app')
@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Stock', 'url' => route('admin.stock-articles.index')]]" current="Détail de l'article" />
    <div class="max-w-xl mx-7">
        <h1 class="text-lg font-semibold mb-4">Détail de l'article du stock</h1>
        <div class="mb-3">
            <strong>Nom :</strong> {{ $stock->name }}
        </div>
        <a href="{{ route('admin.stock.edit', $stock->id) }}" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 font-medium text-sm shadow">Modifier</a>
    </div>
@endsection
