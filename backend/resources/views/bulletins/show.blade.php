{{-- resources/views/bulletins/show.blade.php --}}
    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin - {{ e(($student->firstname ?? '').' '.($student->lastname ?? $student->name ?? '')) }}</title>
    <style>
        body { font-family: Arial, sans-serif; color:#222; }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
        .title { font-size:20px; font-weight:bold; }
        table { border-collapse:collapse; width:100%; margin-top:12px; }
        th, td { border:1px solid #ddd; padding:8px; font-size:14px; }
        th { background:#f5f5f5; text-align:left; }
        .meta { margin-top:8px; font-size:13px; color:#555; }
        .section { margin-top:24px; }
        .grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:8px 24px; }
        .label { color:#555; }
        .value { font-weight:600; }
        .school { font-size:13px; color:#444; }
        .avatar { width:80px; height:80px; object-fit:cover; border-radius:4px; border:1px solid #ddd; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>
<div class="header">
    <div>
        <div class="title">Bulletin de l'élève</div>
        <div class="school">
            École: {{ e(optional($student->school)->name ?? 'N/A') }}
            @php $schoolCode = optional($student->school)->code ?? null; @endphp
            @if(!empty($schoolCode))
                (Code: {{ e($schoolCode) }})
            @endif
        </div>
    </div>
    <div class="meta">
        Généré le: {{ e($generatedAt) }}<br>
        Année scolaire: {{ e(optional(optional($student->registration)->schoolYear)->name ?? ($schoolYearId ?? 'N/A')) }}
    </div>
</div>

{{-- SECTION: Informations élève (provenant de students) --}}
<div class="section">
    <h3>Informations élève</h3>
    <div class="grid">
        <div><span class="label">Nom:</span> <span class="value">{{ e($student->lastname ?? $student->name ?? 'N/A') }}</span></div>
        <div><span class="label">Prénom:</span> <span class="value">{{ e($student->firstname ?? 'N/A') }}</span></div>
        <div><span class="label">Sexe:</span> <span class="value">{{ e(data_get($student->gender, 'value', $student->gender ?? 'N/A')) }}</span></div>
        <div><span class="label">Date de naissance:</span> <span class="value">{{ e(optional($student->birth_date)->format('Y-m-d') ?? 'N/A') }}</span></div>
        <div><span class="label">Lieu de naissance:</span> <span class="value">{{ e($student->birth_place ?? 'N/A') }}</span></div>
        <div><span class="label">Matricule:</span> <span class="value">{{ e($student->matricule ?? 'N/A') }}</span></div>
        <div><span class="label">Numéro permanent:</span> <span class="value">{{ e($student->matricule ?? 'N/A') }}</span></div>
        <div><span class="label">Adresse:</span> <span class="value">{{ e($student->address ?? 'N/A') }}</span></div>
        <div><span class="label">Téléphone:</span> <span class="value">{{ e($student->phone_number ?? 'N/A') }}</span></div>
        <div><span class="label">Email:</span> <span class="value">{{ e($student->email ?? 'N/A') }}</span></div>
        <div>
            <span class="label">Photo:</span>
            @if(!empty($student->image_url))
                <img class="avatar" src="{{ $student->image_url }}" alt="Photo élève">
            @else
                <span class="value">N/A</span>
            @endif
        </div>
    </div>
</div>

{{-- SECTION: Informations d'inscription (registration) --}}
<div class="section">
    <h3>Informations d'inscription</h3>
    <div class="grid">
        <div><span class="label">École:</span> <span class="value">{{ e(optional($student->school)->name ?? 'N/A') }}</span></div>
        <div><span class="label">Classe:</span> <span class="value">{{ e(optional(optional($student->registration)->classroom)->name ?? 'N/A') }}</span></div>
        <div><span class="label">Filière:</span> <span class="value">{{ e(optional(optional($student->registration)->filiaire)->name ?? 'N/A') }}</span></div>
        <div><span class="label">Niveau académique:</span> <span class="value">{{ e(optional(optional($student->registration)->academicLevel)->name ?? 'N/A') }}</span></div>
        <div><span class="label">Cycle:</span> <span class="value">{{ e(optional(optional($student->registration)->cycle)->name ?? 'N/A') }}</span></div>
        <div><span class="label">Année scolaire:</span> <span class="value">{{ e(optional(optional($student->registration)->schoolYear)->name ?? ($schoolYearId ?? 'N/A')) }}</span></div>
        <div><span class="label">Type d'inscription:</span> <span class="value">{{ e(optional(optional($student->registration)->type)->name ?? 'N/A') }}</span></div>
        <div><span class="label">Encadreur:</span> <span class="value">{{ e(optional(optional(optional($student->registration)->personal)->user)->name ?? 'N/A') }}</span></div>
        <div><span class="label">Encadreur (email):</span> <span class="value">{{ e(optional(optional(optional($student->registration)->personal)->user)->email ?? 'N/A') }}</span></div>
    </div>
</div>

{{-- Ancien bloc résumé élève (conservé) --}}
<div class="section">
    <h3>Résumé</h3>
    @php
        $meta = (array) ($student->bulletin_meta ?? []);
        $classSize = $meta['class_size'] ?? null;
        $rank = $meta['rank'] ?? null;
        $overallPercent = $meta['overall_percent'] ?? null;
        $delibStatus = $meta['deliberation'] ?? null; // 'passe' ou 'double'
        $hasRepechage = $meta['has_repechage'] ?? false;

        $firstConduite = $student->conduiteGrades->first();
        $conduiteSem1 = optional(optional($firstConduite?->conduiteSemester1)->semester)->name ?? null;
        $conduiteSem2 = optional(optional($firstConduite?->conduiteSemester2)->semester)->name ?? null;
    @endphp
    <div>
        <strong>Élève:</strong> {{ e(trim(($student->firstname ?? '').' '.($student->name ?? ''))) }}<br>
        <strong>Matricule:</strong> {{ e($student->matricule ?? 'N/A') }}<br>
        <strong>Classe:</strong> {{ e(optional($student->classroom)->name ?? 'N/A') }}<br>
        <strong>Filière:</strong> {{ e(optional($student->filiere)->name ?? 'N/A') }}<br>

        @if(!empty($classSize))
            <strong>Nombre d'élèves de la classe:</strong> {{ (int) $classSize }}<br>
        @endif

        @if(!empty($rank))
            <strong>Place de l'élève:</strong>
            {{ (int) $rank }}@if(!empty($classSize)) / {{ (int) $classSize }} @endif<br>
        @endif

        @if($overallPercent !== null)
            <strong>Pourcentage général:</strong>
            {{ number_format((float) $overallPercent, 2, ',', ' ') }} %<br>
        @endif

        <strong>Application:</strong> N/A<br>

        @if($conduiteSem1 || $conduiteSem2)
            <strong>Conduite:</strong>
            Semestre 1: {{ e($conduiteSem1 ?? 'N/A') }},
            Semestre 2: {{ e($conduiteSem2 ?? 'N/A') }}<br>
        @endif

        @if(!empty($delibStatus))
            <strong>Délibération:</strong> {{ ucfirst($delibStatus) }}<br>
        @endif

        <strong>Repêchage:</strong> {{ $hasRepechage ? 'Oui' : 'Non' }}
    </div>
</div>

{{-- SECTION: Notes --}}
<div class="section">
    <h3>Notes</h3>
    <table>
        <thead>
        <tr>
            <th>Matière</th>
            <th>Coef</th>
            <th>Note</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @php $sumTotal = 0; $sumCoef = 0; @endphp
        @forelse($student->notes as $n)
            @php
                $coef = (float)($n->coef ?? 1);
                $val = (float)($n->value ?? 0);
                $tot = $coef * $val;
                $sumTotal += $tot;
                $sumCoef += $coef;
            @endphp
            <tr>
                <td>{{ e(optional($n->course)->name ?? 'N/A') }}</td>
                <td>{{ number_format($coef, 2, ',', ' ') }}</td>
                <td>{{ number_format($val, 2, ',', ' ') }}</td>
                <td>{{ number_format($tot, 2, ',', ' ') }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Aucune note</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <th colspan="3">Somme des coefs</th>
            <th>{{ number_format($sumCoef, 2, ',', ' ') }}</th>
        </tr>
        <tr>
            <th colspan="3">Moyenne pondérée</th>
            <th>
                @php $avg = $sumCoef > 0 ? $sumTotal / $sumCoef : 0; @endphp
                {{ number_format($avg, 2, ',', ' ') }}
            </th>
        </tr>
        </tfoot>
    </table>
</div>

{{-- SECTION: Conduite --}}
<div class="section">
    <h3>Conduite</h3>
    <table>
        <thead>
        <tr>
            <th>Fautes</th>
            <th>Semestre 1</th>
            <th>Semestre 2</th>
        </tr>
        </thead>
        <tbody>
        @forelse($student->conduiteGrades as $c)
            <tr>
                <td>{{ e((string)($c->fault_count ?? 0)) }}</td>
                <td>{{ e(optional(optional($c->conduiteSemester1)->semester)->name ?? 'N/A') }}</td>
                <td>{{ e(optional(optional($c->conduiteSemester2)->semester)->name ?? 'N/A') }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Aucune donnée de conduite</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- SECTION: Délibérations --}}
<div class="section">
    <h3>Délibérations</h3>
    <table>
        <thead>
        <tr>
            <th>Moyenne</th>
            <th>Décision</th>
            <th>Année</th>
        </tr>
        </thead>
        <tbody>
        @forelse($student->deliberations as $d)
            <tr>
                <td>{{ e((string)($d->average ?? 'N/A')) }}</td>
                <td>{{ e($d->decision ?? 'N/A') }}</td>
                <td>{{ e((string)($d->school_year_id ?? 'N/A')) }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Aucune délibération</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="section no-print">
    <button onclick="window.print()">Imprimer</button>
</div>
</body>
</html>
