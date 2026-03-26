@extends('backend.layouts.app')
@section('admin-content')
    <x-breadcrumb :links="[['label' => 'Dashboard', 'url' => route('admin.dashboard')], ['label' => 'Stock', 'url' => route('admin.stock-articles.index')]]" current="Modifier l'article" />
    <div class="max-w-xl mx-7">
        <h1 class="text-lg font-semibold mb-4">Modifier l'article du stock</h1>
        <form method="POST" action="{{ route('admin.stock.update', $stock->id) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Nom de l'article</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $stock->name }}" required>
            </div>
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 font-medium text-sm shadow">Mettre à jour</button>
        </form>
    </div>
@endsection
