<html>
<head>
    <title>Export Repêchage</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Liste des repêchages</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nom complet</th>
                <th>Classe</th>
                <th>Filière</th>
                <th>Cycle</th>
                <th>Niveau académique</th>
                <th>Score (%)</th>
                <th>Score élève</th>
                <th>Éliminé</th>
                <th>Date création</th>
            </tr>
        </thead>
        <tbody>
        @foreach($repechages as $rep)
            <tr>
                <td>{{ $rep->id }}</td>
                <td>{{ $rep->full_name }}</td>
                <td>{{ optional($rep->classroom)->name }}</td>
                <td>{{ optional($rep->filiaire)->name }}</td>
                <td>{{ optional($rep->cycle)->name }}</td>
                <td>{{ optional($rep->academicLevel)->name }}</td>
                <td>{{ $rep->score_percent }}</td>
                <td>{{ $rep->student_score }}</td>
                <td>{{ $rep->is_eliminated ? 'Oui' : 'Non' }}</td>
                <td>{{ $rep->created_at }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
