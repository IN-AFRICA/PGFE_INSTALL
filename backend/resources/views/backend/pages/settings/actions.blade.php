@extends('backend.layouts.app')

@section('content')
<div class="container">
    <h1>Actions des routes</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>URI</th>
                <th>Méthodes</th>
                <th>Action</th>
                <th>Middleware</th>
                <th>Contrôles</th>
            </tr>
        </thead>
        <tbody>
        @foreach($routes as $r)
            <tr>
                <td>{{ $r['uri'] }}</td>
                <td>{{ implode(', ', $r['methods']) }}</td>
                <td>{{ $r['action'] }}</td>
                <td>{{ implode(', ', $r['middleware']) }}</td>
                <td>
                    @if(in_array('GET', $r['methods']))
                        <a href="/{{ $r['uri'] }}" class="btn btn-sm btn-primary" target="_blank">Ouvrir</a>
                    @endif

                    @if(in_array('POST', $r['methods']))
                        <form method="POST" action="/{{ $r['uri'] }}" style="display:inline-block">
                            @csrf
                            <button class="btn btn-sm btn-warning">POST</button>
                        </form>
                    @endif

                    @if(in_array('DELETE', $r['methods']))
                        <form method="POST" action="/{{ $r['uri'] }}" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">DELETE</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
