<html>
<head>
    <title>Export Étudiants</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Liste des étudiants</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Genre</th>
                <th>Date naissance</th>
                <th>Email</th>
                <th>Adresse</th>
            </tr>
        </thead>
        <tbody>
        @foreach($students as $student)
            <tr>
                <td>{{ $student->id }}</td>
                <td>{{ $student->matricule }}</td>
                <td>{{ $student->lastname }}</td>
                <td>{{ $student->firstname }}</td>
                <td>{{ $student->gender }}</td>
                <td>{{ $student->birth_date }}</td>
                <td>{{ $student->email }}</td>
                <td>{{ $student->address }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
