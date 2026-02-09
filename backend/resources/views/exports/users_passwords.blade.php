<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Utilisateurs & Rôles — Export</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 6px; }
        .meta { color: #555; font-size: 11px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .roles { color: #222; }
        .unknown { color: #b00; font-style: italic; }
        .known { color: #064; }
    </style>
    <!-- Note: Les mots de passe affichés proviennent des seeders connus uniquement. -->
    <!-- Les autres sont marqués comme inconnus car non récupérables (hash). -->
    <!-- Conformité: option 1 (aucune réinitialisation). -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' data:;">
    <meta name="robots" content="noindex,nofollow">
</head>
<body>
    <h1>Export des utilisateurs, rôles et mots de passe (option 1)</h1>
    <div class="meta">Généré le: {{ $generatedAt }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle(s)</th>
                <th>Mot de passe</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['email'] }}</td>
                <td class="roles">{{ implode(', ', $row['roles']) }}</td>
                @php $isUnknown = str_starts_with($row['password'], 'inconnu'); @endphp
                <td class="{{ $isUnknown ? 'unknown' : 'known' }}">{{ $row['password'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p style="margin-top:12px;color:#666;font-size:11px;">
        Note: Seuls les comptes seedés ont un mot de passe par défaut connu. Les autres comptes
        affichent « inconnu (hash non récupérable) ». Pour un export avec mots de passe pour tous
        les comptes, il faudrait procéder à une réinitialisation contrôlée (non effectuée ici).
    </p>
</body>
</html>
