@extends('backend.layouts.app')
@section('admin-content')
    <div class="max-w-xl mx-7">
        <h1 class="text-lg font-semibold mb-4">Modifier l'infrastructure</h1>
        <form method="POST" action="{{ route('admin.infrastructures.update', $infrastructure->id) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Nom</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $infrastructure->name }}" required>
            </div>
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 font-medium text-sm shadow">Mettre à jour</button>
        </form>
    </div>
@endsection
