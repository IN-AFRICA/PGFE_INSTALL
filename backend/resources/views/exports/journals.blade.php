<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Export Journaux</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { font-size: 12px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 8px; }
        th { background: #f0f0f0; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Journal des écritures</h1>

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
                <th>Description</th>
                <th class="text-right">Montant</th>
                <th>Compte</th>
                <th>Input</th>
                <th>Output</th>
                <th>Plan</th>
                <th>Sous-plan</th>
            </tr>
        </thead>
        <tbody>
        @forelse($journals as $j)
            <tr>
                <td>{{ $j->date }}</td>
                <td>{{ $j->description }}</td>
                <td class="text-right">{{ number_format((float)$j->montant, 2, ',', ' ') }}</td>
                <td>{{ optional($j->account)->name }}</td>
                <td>{{ optional($j->inputAccount)->name }}</td>
                <td>{{ optional($j->outputAccount)->name }}</td>
                <td>{{ optional($j->accountPlan)->name }}</td>
                <td>{{ optional($j->subAccountPlan)->name }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">Aucune écriture trouvée</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>

