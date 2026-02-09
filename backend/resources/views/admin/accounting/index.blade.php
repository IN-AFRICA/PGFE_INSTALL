@extends('layouts.admin')
@section('content')
<div class="container"><h1>Comptabilité</h1><a href="{{ route('accounting.create') }}" class="btn btn-primary mb-3">Ajouter</a><table class="table table-bordered"><thead><tr><th>ID</th><th>Libellé</th><th>Montant</th><th>Date</th><th>Actions</th></tr></thead><tbody><!-- Comptabilité loop here --></tbody></table></div>@endsection