<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Export Présences</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { font-size: 12px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 8px; }
        th { background: #f0f0f0; text-align: left; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Présences</h1>

    <div class="meta">
        @php
            $d1 = $date_debut ?? ($date ?? null);
            $d2 = $date_fin ?? ($date ?? null);
        @endphp
        @if($d1 && $d2 && $d1 !== $d2)
            Période: {{ $d1 }} au {{ $d2 }}
        @elseif($d1)
            Date: {{ $d1 }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>École</th>
                <th>Classe</th>
                <th>Élève</th>
                <th class="text-center">Présence</th>
            </tr>
        </thead>
        <tbody>
        @forelse($presences as $row)
            @php
                $studentNameParts = [];
                if(optional($row->student)->lastname) $studentNameParts[] = $row->student->lastname;
                if(optional($row->student)->firstname) $studentNameParts[] = $row->student->firstname;
                if(optional($row->student)->name) $studentNameParts[] = $row->student->name;
                $studentName = count($studentNameParts) ? implode(' ', $studentNameParts) : (optional($row->student)->id ?? '');

                // Nouvelle priorité: 1) absent_justified => Absence justifiée
                // 2) presence == 0 => Absence non justifiée
                // 3) sick => Malade
                // 4) sinon => Présent
                $status = 'Présent';
                if (! empty($row->absent_justified)) {
                    $status = 'Absence justifiée';
                } elseif ($row->presence === false || (is_numeric($row->presence) && (int) $row->presence === 0)) {
                    $status = 'Absence non justifiée';
                } elseif (! empty($row->sick)) {
                    $status = 'Malade';
                }
            @endphp
            <tr>
                <td>{{ optional($row->created_at)?->format('Y-m-d') }}</td>
                <td>{{ optional($row->school)->name }}</td>
                <td>{{ optional($row->classroom)->name }}</td>
                <td>{{ $studentName }}</td>
                <td class="text-center">{{ $status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Aucune donnée trouvée</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
