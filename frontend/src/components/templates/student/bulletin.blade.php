{{-- 
    Bulletin Scolaire - Template Blade pour génération PDF
    Ce fichier représente le bulletin complet à générer côté backend.
    
    Variables attendues (à passer au template):
    - $student: StudentInfo (full_name, gender, birth_place, birth_date, matricule)
    - $school: Ecole (name, city, code, province->name)
    - $registration: Registration (classroom->name, filiere->name, academic_level->name, school_year->name)
    - $grades: array de Grade (course, note, maxima, sem1_total, sem2_total, total_obtained, total_maxima)
    - $deliberations: array (decision)
    - $groupedGrades: grades groupés par total_maxima (préparé côté backend)
    - $generalTotals: totaux généraux calculés (maxima, obtained)
    - $elevePasseClasse: boolean (true si l'élève passe)
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin Scolaire - {{ $student->full_name ?? 'Élève' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        .border { border: 1px solid black; }
        .p-1 { padding: 4px; }
        .p-2 { padding: 8px; }
        .p-4 { padding: 16px; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-gray-200 { background-color: #e5e7eb; }
        .bg-gray-300 { background-color: #d1d5db; }
        .bg-black { background-color: #000; }
        .w-full { width: 100%; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-center { align-items: center; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; }
        .space-y-3 > * + * { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mt-2 { margin-top: 8px; }
        .mb-2 { margin-bottom: 8px; }
        .w-20 { width: 80px; }
        .opacity-50 { opacity: 0.5; }
        .line-through { text-decoration: line-through; }
        
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 4px; text-align: center; }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; padding: 16px; border: 1px solid black; }
        .header-img { width: 80px; height: auto; }
        .header-text { flex-grow: 1; text-align: center; font-weight: bold; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; }
        .info-box { border: 1px solid black; padding: 16px; }
        
        .number-grid { display: flex; gap: 2px; }
        .number-box { width: 20px; height: 20px; border: 1px solid black; text-align: center; font-size: 8pt; }
        
        .footer-section { padding: 16px; border: 1px solid black; margin-top: 8px; }
        .footer-notes { font-size: 7pt; border-top: 1px solid black; padding-top: 4px; margin-top: 8px; }
        
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

{{-- ==================== ZONE 1: EN-TÊTE NATIONAL ==================== --}}
<div class="header-flex">
    <img src="{{ asset('images/flag.png') }}" alt="Drapeau RDC" class="header-img">
    <div class="header-text">
        <div>REPUBLIQUE DEMOCRATIQUE DU CONGO</div>
        <div>MINISTERE DE L'EDUCATION NATIONALE ET NOUVELLE CITOYENNETE</div>
    </div>
    <img src="{{ asset('images/coat.png') }}" alt="Armoiries RDC" class="header-img">
</div>

{{-- ==================== ZONE 2: N° ID ==================== --}}
<div class="border p-4" style="font-size: 9pt;">
    <div class="flex justify-between items-center">
        <span class="font-bold" style="font-size: 12pt;">N° ID.</span>
        <div class="number-grid">
            @for($i = 0; $i < 10; $i++)
                <div class="number-box">{{ $numeroId[$i] ?? '' }}</div>
            @endfor
        </div>
    </div>
</div>

{{-- ==================== ZONE 3: INFORMATIONS ÉCOLE & ÉLÈVE ==================== --}}
<div class="info-grid">
    {{-- Colonne gauche: Infos école --}}
    <div class="info-box space-y-3">
        <p><strong>PROVINCE EDUC.:</strong> {{ $school->province->name ?? '' }}</p>
        <p><strong>VILLE:</strong> {{ $school->city ?? '' }}</p>
        <p><strong>COMMUNE / TER.:</strong> {{ $school->commune ?? '' }}</p>
        <p><strong>ECOLE:</strong> {{ $school->name ?? '' }}</p>
        <div class="mt-4">
            <strong>CODE:</strong>
            <div class="number-grid" style="display: inline-flex; margin-left: 8px;">
                @php $codeDigits = str_split(str_pad($school->code ?? '', 12, ' ', STR_PAD_RIGHT)); @endphp
                @foreach($codeDigits as $digit)
                    <div class="number-box">{{ $digit }}</div>
                @endforeach
            </div>
        </div>
    </div>
    
    {{-- Colonne droite: Infos élève --}}
    <div class="info-box space-y-3">
        <p><strong>CLASSE:</strong> {{ $registration->classroom->name ?? '' }}</p>
        <p>
            <strong>ELEVE:</strong> {{ $student->full_name ?? '' }}
            <span style="margin-left: 16px;"><strong>SEXE:</strong> {{ strtoupper(substr($student->gender ?? '', 0, 1)) }}</span>
        </p>
        <p>
            <strong>Né(e) à:</strong> {{ $student->birth_place ?? '' }}
            <span style="margin-left: 16px;"><strong>LE:</strong> {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '' }}</span>
        </p>
        <div class="mt-4">
            <strong>N° PERM:</strong>
            <div class="number-grid" style="display: inline-flex; margin-left: 8px;">
                @php $matriculeDigits = str_split(str_pad($student->matricule ?? '', 14, ' ', STR_PAD_RIGHT)); @endphp
                @foreach($matriculeDigits as $digit)
                    <div class="number-box">{{ $digit }}</div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ==================== ZONE 4: TITRE DU BULLETIN ==================== --}}
<div class="border p-4 text-center bg-gray-200" style="font-size: 11pt;">
    <strong class="uppercase">
        BULLETIN DE LA {{ $registration->academic_level->name ?? '' }} HUMANITES / {{ $registration->filiere->name ?? '' }}<br>
        ANNEE SCOLAIRE {{ $registration->school_year->name ?? '' }}
    </strong>
</div>

{{-- ==================== ZONE 5: TABLEAU DES NOTES ==================== --}}
<table style="font-size: 9pt;">
    <thead class="bg-gray-300 font-bold">
        <tr>
            <th rowspan="3" style="vertical-align: middle;">BRANCHES</th>
            <th colspan="4">PREMIER SEMESTRE</th>
            <th colspan="4">DEUXIEME SEMESTRE</th>
            <th rowspan="3" style="width: 40px; vertical-align: middle;">TG</th>
            <th rowspan="3" class="bg-black" style="width: 10px;"></th>
            <th colspan="2">Trav Journal</th>
        </tr>
        <tr>
            <th colspan="2">Trav Journal</th>
            <th rowspan="2" style="width: 40px;">Exam</th>
            <th rowspan="2" style="width: 40px;">TOT</th>
            <th colspan="2">Trav Journal</th>
            <th rowspan="2" style="width: 40px;">Exam</th>
            <th rowspan="2" style="width: 40px;">TOT</th>
            <th rowspan="2" style="width: 30px;">%</th>
            <th rowspan="2" style="width: 80px;">Sign prof</th>
        </tr>
        <tr>
            <th style="width: 40px;">1ère P</th>
            <th style="width: 40px;">2ème P</th>
            <th style="width: 40px;">3ème P</th>
            <th style="width: 40px;">4ème P</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedGrades as $totalMax => $groupGrades)
            {{-- Ligne MAXIMA pour ce groupe --}}
            <tr class="bg-gray-300 font-bold">
                <th class="text-left uppercase">MAXIMA</th>
                <td>{{ $groupGrades[0]->maxima->P1 ?? '' }}</td>
                <td>{{ $groupGrades[0]->maxima->P2 ?? '' }}</td>
                <td>{{ $groupGrades[0]->maxima->E1 ?? '' }}</td>
                <td>{{ $groupGrades[0]->sem1_maxima ?? '' }}</td>
                <td>{{ $groupGrades[0]->maxima->P3 ?? '' }}</td>
                <td>{{ $groupGrades[0]->maxima->P4 ?? '' }}</td>
                <td>{{ $groupGrades[0]->maxima->E2 ?? '' }}</td>
                <td>{{ $groupGrades[0]->sem2_maxima ?? '' }}</td>
                <td>{{ $groupGrades[0]->total_maxima ?? '' }}</td>
                <td class="bg-black"></td>
                <td class="bg-black" colspan="2"></td>
            </tr>
            
            {{-- Lignes des cours --}}
            @foreach($groupGrades as $grade)
                <tr>
                    <td class="text-left font-bold">{{ $grade->course }}</td>
                    <td>{{ $grade->note->P1 ?? '' }}</td>
                    <td>{{ $grade->note->P2 ?? '' }}</td>
                    <td>{{ $grade->note->E1 ?? '' }}</td>
                    <td class="bg-gray-100 font-bold">{{ $grade->sem1_total ?? '-' }}</td>
                    <td>{{ $grade->note->P3 ?? '' }}</td>
                    <td>{{ $grade->note->P4 ?? '' }}</td>
                    <td>{{ $grade->note->E2 ?? '' }}</td>
                    <td class="bg-gray-100 font-bold">{{ $grade->sem2_total ?? '-' }}</td>
                    <td class="bg-gray-200 font-bold">{{ $grade->total_obtained ?? '' }}</td>
                    <td class="bg-black"></td>
                    <td></td>
                    <td colspan="2"></td>
                </tr>
            @endforeach
        @endforeach
        
        {{-- MAXIMA GENERAUX --}}
        <tr class="font-bold bg-gray-200">
            <td class="text-left">MAXIMA GENERAUX</td>
            <td>{{ $generalTotals['maxima']['P1'] ?? 0 }}</td>
            <td>{{ $generalTotals['maxima']['P2'] ?? 0 }}</td>
            <td>{{ $generalTotals['maxima']['E1'] ?? 0 }}</td>
            <td>{{ $generalTotals['maxima']['TOT1'] ?? 0 }}</td>
            <td>{{ $generalTotals['maxima']['P3'] ?? 0 }}</td>
            <td>{{ $generalTotals['maxima']['P4'] ?? 0 }}</td>
            <td>{{ $generalTotals['maxima']['E2'] ?? 0 }}</td>
            <td>{{ $generalTotals['maxima']['TOT2'] ?? 0 }}</td>
            <td>{{ $generalTotals['maxima']['TG'] ?? 0 }}</td>
            <td class="bg-black" colspan="3"></td>
        </tr>
        
        {{-- TOTAUX --}}
        <tr class="font-bold bg-gray-100">
            <td class="text-left">TOTAUX</td>
            <td>{{ $generalTotals['obtained']['P1'] ?? 0 }}</td>
            <td>{{ $generalTotals['obtained']['P2'] ?? 0 }}</td>
            <td>{{ $generalTotals['obtained']['E1'] ?? 0 }}</td>
            <td>{{ $generalTotals['obtained']['S1'] ?? 0 }}</td>
            <td>{{ $generalTotals['obtained']['P3'] ?? 0 }}</td>
            <td>{{ $generalTotals['obtained']['P4'] ?? 0 }}</td>
            <td>{{ $generalTotals['obtained']['E2'] ?? 0 }}</td>
            <td>{{ $generalTotals['obtained']['S2'] ?? 0 }}</td>
            <td>{{ $generalTotals['obtained']['TG'] ?? 0 }}</td>
            <td class="bg-black" colspan="3"></td>
        </tr>
    </tbody>
    
    {{-- TFOOT: POURCENTAGE, PLACE, APPLICATION, CONDUITE, SIGNATURE --}}
    <tfoot>
        <tr>
            <td class="text-left">POURCENTAGE</td>
            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            <td class="bg-black"></td>
            <td class="text-left p-2" rowspan="5" colspan="2" style="vertical-align: top; font-size: 8pt;">
                <ul style="list-style: none; padding: 8px;">
                    <li style="{{ $elevePasseClasse ? '' : 'text-decoration: line-through; opacity: 0.5;' }}">
                        PASSE (1) @if($elevePasseClasse) <strong style="font-size: 12pt;">✓</strong> @endif
                    </li>
                    <li style="{{ !$elevePasseClasse ? '' : 'text-decoration: line-through; opacity: 0.5;' }}">
                        DOUBLE (1) @if(!$elevePasseClasse && count($grades) > 0) <strong style="font-size: 12pt;">✓</strong> @endif
                    </li>
                    <li style="margin-top: 12px;">Le {{ now()->format('d/m/Y') }}</li>
                    <li style="margin-top: 12px;">Le Chef d'Etablissement<br>Sceau de l'école</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td class="text-left">PLACE / NBRE D'ELEVES</td>
            <td>/</td><td>/</td><td>/</td><td>/</td><td>/</td><td>/</td><td>/</td><td>/</td><td>/</td>
            <td class="bg-black"></td>
        </tr>
        <tr>
            <td class="text-left">APPLICATION</td>
            <td></td><td></td>
            <td class="bg-gray-200" colspan="2" rowspan="2"></td>
            <td></td><td></td>
            <td class="bg-gray-200" colspan="3" rowspan="2"></td>
            <td class="bg-black"></td>
        </tr>
        <tr>
            <td class="text-left">CONDUITE</td>
            <td></td><td></td><td></td><td></td>
            <td class="bg-black"></td>
        </tr>
        <tr>
            <td class="text-left">Signat. du resp.</td>
            <td></td><td></td>
            <td class="bg-gray-100" colspan="2"></td>
            <td></td><td></td><td></td>
            <td class="bg-gray-100" colspan="2"></td>
            <td class="bg-black"></td>
        </tr>
    </tfoot>
</table>

{{-- ==================== ZONE 6: PIED DE PAGE ==================== --}}
<div class="footer-section">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; font-size: 9pt;">
        <div class="text-left">
            <div style="{{ $elevePasseClasse ? '' : 'text-decoration: line-through; opacity: 0.5;' }}">
                - L'élève passe dans la classe supérieure (1) @if($elevePasseClasse) <strong style="font-size: 14pt;">✓</strong> @endif
            </div>
            <div style="{{ !$elevePasseClasse ? '' : 'text-decoration: line-through; opacity: 0.5;' }}">
                - L'élève double la classe (1) @if(!$elevePasseClasse && count($grades) > 0) <strong style="font-size: 14pt;">✓</strong> @endif
            </div>
            <div style="margin-top: 20px;"><strong>Signature de l'élève</strong></div>
        </div>
        <div class="text-center" style="flex: 1;">
            <strong>Sceau de l'Ecole</strong>
        </div>
        <div class="text-center" style="flex: 1;">
            Fait à <span class="uppercase">{{ $school->city ?? '' }}</span>, le {{ now()->format('d/m/Y') }}<br><br>
            <strong>Chef d'Etablissement</strong><br>
            (Noms & Signature)
        </div>
    </div>
    
    <div class="footer-notes">
        <div>(1) Biffer la mention inutile.</div>
        <div>NOTE IMPORTANTE : Le bulletin est sans valeur s'il est raturé ou surchargé.</div>
    </div>
    <div class="text-center" style="font-style: italic; margin-top: 4px; font-size: 8pt;">
        Interdiction formelle de reproduire ce bulletin sous peine des sanctions prévues par la loi.
    </div>
</div>

</body>
</html>
