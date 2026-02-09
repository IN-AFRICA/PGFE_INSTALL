<html>
<head>
    <title>Export Inscriptions</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Liste des inscriptions</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date inscription</th>
                <th>Statut</th>
                <th>Année scolaire</th>
                <th>Classe</th>
                <th>Niveau académique</th>
                <th>Cycle</th>
                <th>Filière</th>
                <th>Note</th>

                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Genre</th>
                <th>Date de naissance</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th>Email</th>

                <th>Parent 1 (Nom)</th>
                <th>Parent 1 (Téléphone)</th>
                <th>Parent 1 (Email)</th>
                <th>Parent 2 (Nom)</th>
                <th>Parent 2 (Téléphone)</th>
                <th>Parent 2 (Email)</th>
                <th>Parent 3 (Nom)</th>
                <th>Parent 3 (Téléphone)</th>
                <th>Parent 3 (Email)</th>
            </tr>
        </thead>
        <tbody>
        @foreach($registrations as $reg)
            <tr>
                <td>{{ $reg->id }}</td>
                <td>{{ $reg->registration_date }}</td>
                <td>{{ $reg->registration_status ? 'Actif' : 'Inactif' }}</td>
                <td>{{ optional($reg->schoolYear)->name }}</td>
                <td>{{ optional($reg->classroom)->name }}</td>
                <td>{{ optional($reg->academicLevel)->name }}</td>
                <td>{{ optional($reg->cycle)->name }}</td>
                <td>{{ optional($reg->filiaire)->name }}</td>
                <td>{{ $reg->note }}</td>

                <td>{{ optional($reg->student)->matricule }}</td>
                <td>{{ optional($reg->student)->lastname }}</td>
                <td>{{ optional($reg->student)->firstname }}</td>
                <td>{{ optional($reg->student)->gender }}</td>
                <td>{{ optional($reg->student)->birth_date }}</td>
                <td>{{ optional($reg->student)->address }}</td>
                <td>{{ optional($reg->student)->phone_number }}</td>
                <td>{{ optional($reg->student)->email }}</td>

                <td>{{ optional($reg->registrationParents?->parent1)->name }}</td>
                <td>{{ optional($reg->registrationParents?->parent1)->phone1 }}</td>
                <td>{{ optional($reg->registrationParents?->parent1)->email1 }}</td>
                <td>{{ optional($reg->registrationParents?->parent2)->name }}</td>
                <td>{{ optional($reg->registrationParents?->parent2)->phone1 }}</td>
                <td>{{ optional($reg->registrationParents?->parent2)->email1 }}</td>
                <td>{{ optional($reg->registrationParents?->parent3)->name }}</td>
                <td>{{ optional($reg->registrationParents?->parent3)->phone1 }}</td>
                <td>{{ optional($reg->registrationParents?->parent3)->email1 }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
