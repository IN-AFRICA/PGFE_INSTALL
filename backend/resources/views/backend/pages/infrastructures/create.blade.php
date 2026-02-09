@extends('backend.layouts.app')
@section('admin-content')
    <div class="max-w-xl mx-7">
        <h1 class="text-lg font-semibold mb-4">Créer une infrastructure</h1>
        <form method="POST" action="{{ route('admin.infrastructures.store') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nom</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 font-medium text-sm shadow">Enregistrer</button>
        </form>
    </div>
@endsection
