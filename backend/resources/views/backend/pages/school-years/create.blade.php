@extends('backend.layouts.app')
@section('admin-content')
    <div class="container">
        <h1>Créer une année scolaire</h1>
        <form method="POST" action="{{ route('admin.school-years.store') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nom de l'année scolaire</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Créer</button>
        </form>
    </div>
@endsection
