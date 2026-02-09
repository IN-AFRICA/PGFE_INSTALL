<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Présences du personnel</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .meta { margin-bottom: 12px; }
    </style>
    </head>
<body>
<h1>Présences du personnel</h1>

<div class="meta">
    @php
        $d1 = $date_debut ?? null;
        $d2 = $date_fin ?? null;
        if (!$d1 && !$d2) {
            // aucune plage fournie, afficher la date de chaque ligne
        }
    @endphp
    @if($d1 && $d2)
        <div>Période: {{ $d1 }} au {{ $d2 }}</div>
    @elseif($d1)
        <div>Date: {{ $d1 }}</div>
    @endif
    <div>Total: {{ count($presences ?? []) }}</div>
    </div>

<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>École</th>
        <th>Personnel</th>
        <th>Présence</th>
    </tr>
    </thead>
    <tbody>
    @forelse($presences as $row)
        @php
            $person = $row->personnel ?? null;
            $nameParts = array_filter([
                $person->name ?? null,
                $person->post_name ?? null,
                $person->pre_name ?? null,
            ]);
            $personName = $nameParts ? implode(' ', $nameParts) : ($person->id ?? '');
            $status = ($row->presence === true || (is_numeric($row->presence) && (int)$row->presence === 1)) ? 'Présent' : 'Absent';
        @endphp
        <tr>
            <td>{{ optional($row->created_at)->toDateString() }}</td>
            <td>{{ $row->school->name ?? '' }}</td>
            <td>{{ $personName }}</td>
            <td>{{ $status }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="4" style="text-align: center;">Aucune donnée</td>
        </tr>
    @endforelse
    </tbody>
    </table>

</body>
</html>
