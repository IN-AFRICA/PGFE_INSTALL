<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulletins de la classe</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .bulletin { page-break-after: always; margin-bottom: 30px; }
        h2 { margin-bottom: 0; }
        .info { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
    </style>
</head>
<body>
@foreach($bulletins as $bulletin)
    <div class="bulletin">
        <h2>Bulletin de {{ $bulletin->student_name ?? 'Élève' }}</h2>
        <div class="info">
            Classe : {{ $classroom->name }}<br>
            Année scolaire : {{ $bulletin->school_year ?? '' }}<br>
            N° : {{ $bulletin->id }}
        </div>
        <table>
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Note</th>
                    <th>Observation</th>
                </tr>
            </thead>
            <tbody>
            @foreach($bulletin->subjects ?? [] as $subject)
                <tr>
                    <td>{{ $subject['name'] ?? '' }}</td>
                    <td>{{ $subject['grade'] ?? '' }}</td>
                    <td>{{ $subject['observation'] ?? '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div>Appréciation : {{ $bulletin->appreciation ?? '' }}</div>
    </div>
@endforeach
</body>
</html>
