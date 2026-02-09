<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bulletin Scolaire - {{ $student['full_name'] }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
            text-align: center;
        }
        .bordered {
            border: 1px solid black;
        }
        .bordered th, .bordered td {
            border: 1px solid black;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-gray-200 { background-color: #e5e7eb; }
        .bg-gray-300 { background-color: #d1d5db; }
        .bg-black { background-color: black; color: white; }
        .p-1 { padding: 4px; }
        .p-05 { padding: 2px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .w-full { width: 100%; }
        
        .dotted-line {
            border-bottom: 2px dotted black;
            display: inline-block;
            width: 100%;
            height: 14px;
        }
        .input-value {
            font-family: monospace;
            font-size: 12px;
            font-weight: bold;
            padding-left: 5px;
        }
        
        /* Specific adjustments for DomPDF */
        .page-break {
            page-break-after: always;
        }
        .logo {
            width: 50px;
            height: auto;
        }
        
        /* Grid substitution using tables */
        .info-grid td {
            vertical-align: top;
            padding: 5px;
            border: 1px solid black;
        }
        
        .square-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid black;
            text-align: center;
            line-height: 15px;
            margin-right: 2px;
            font-size: 9px;
        }
    </style>
    
    <?php
        $fmt = function($val) {
            if ($val === null || $val === '') return '';
            $n = is_numeric($val) ? floatval($val) : 0;
            return number_format($n, 1, '.', '');
        };

        $dateImpression = $generatedAt ?? date('d/m/Y');

        // Les totaux généraux sont strictement ceux du backend
        $generalTotals = $summary['period_exam_totals'] ?? [ 'maxima' => [], 'obtained' => [] ];

        // Grouper les notes par total_maxima (toujours dynamique)
        $groupedGrades = [];
        if (isset($grades) && is_array($grades)) {
            foreach ($grades as $grade) {
                $key = $grade['total_maxima'] ?? 0;
                if (!isset($groupedGrades[$key])) {
                    $groupedGrades[$key] = [];
                }
                $groupedGrades[$key][] = $grade;
            }
        }

        $periodKeys = ['P1', 'P2', 'E1', 'TOT1', 'P3', 'P4', 'E2', 'TOT2', 'TG'];
        $noteKeys = ['P1', 'P2', 'E1', 'S1', 'P3', 'P4', 'E2', 'S2', 'TG'];

        $getPeriodKey = function($i) use ($periodKeys) {
            return $periodKeys[$i - 1] ?? '';
        };

        $getNoteKey = function($i) use ($noteKeys) {
            return $noteKeys[$i - 1] ?? '';
        };

        // Maxima strictement depuis le backend pour chaque colonne
        $getGroupMaxima = function($grade, $key) {
            if (!$grade) return '';
            if ($key === 'TOT1') return isset($grade['sem1_maxima']) ? $grade['sem1_maxima'] : '';
            if ($key === 'TOT2') return isset($grade['sem2_maxima']) ? $grade['sem2_maxima'] : '';
            if ($key === 'TG') return isset($grade['total_maxima']) ? $grade['total_maxima'] : '';
            return isset($grade['maxima'][$key]) ? $grade['maxima'][$key] : '';
        };

        // Pourcentage par période/examen depuis le backend
        $getPeriodPercent = function($key) use ($summary) {
            if (isset($summary['period_exam_place'][$key]['percent'])) {
                return $summary['period_exam_place'][$key]['percent'] . '%';
            }
            return '';
        };

        // Place par période/examen depuis le backend
        $getPeriodPlace = function($key) use ($summary) {
            if (isset($summary['period_exam_place'][$key]['label'])) {
                return $summary['period_exam_place'][$key]['label'];
            }
            return '';
        };

        // Statut passage/doublement strictement dynamique
        $elevePasseClasse = ($summary['deliberation'] ?? null) === 'passe';
        $elevePasse = ($summary['deliberation'] ?? null) === 'passe';

        // Helper pour split string en array
        $schoolCodeArray = isset($school['code']) ? str_split($school['code']) : [];
        $permIdArray = isset($student['matricule']) ? str_split($student['matricule']) : [];
    ?>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="header-table bordered mb-2" style="border: 1px solid black;">
            <tr>
                <td style="width: 15%; padding: 5px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6f/Flag_of_the_Democratic_Republic_of_the_Congo.svg" class="logo" alt="RDC Flag">
                </td>
                <td style="width: 70%; padding: 5px;">
                    <div style="font-size: 12px; font-weight: bold;">REPUBLIQUE DEMOCRATIQUE DU CONGO</div>
                    <div style="font-size: 10px;">MINISTERE DE L'EDUCATION NATIONALE ET NOUVELLE CITOYENNETE</div>
                </td>
                <td style="width: 15%; padding: 5px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/1/1b/Coat_of_arms_of_the_Democratic_Republic_of_the_Congo.svg" class="logo" alt="RDC Coat of Arms">
                </td>
            </tr>
        </table>

        <!-- ID Section -->
        <table class="bordered mb-2" style="border: 1px solid black;">
            <tr>
                <td class="p-1">
                    <span class="font-bold">N° ID.</span>
                    <span style="float: right;">
                        @for($i = 1; $i <= 20; $i++)
                        <span class="square-box"></span>
                        @endfor
                    </span>
                </td>
            </tr>
        </table>

        <!-- Info Grid -->
        <table class="info-grid mb-2" style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <!-- Province -->
                    <div class="mb-2">
                        <span class="font-bold">PROVINCE EDUC. : </span>
                        <span class="dotted-line" style="width: 65%;"><span class="input-value">{{ $school['province'] ?? '' }}</span></span>
                    </div>
                    <!-- Ville -->
                    <div class="mb-2">
                        <span class="font-bold">VILLE : </span>
                        <span class="dotted-line" style="width: 80%;"><span class="input-value">{{ $school['city'] ?? '' }}</span></span>
                    </div>
                    <!-- Commune -->
                    <div class="mb-2">
                        <span class="font-bold">COMMUNE / TER. : </span>
                        <span class="dotted-line" style="width: 60%;"><span class="input-value">{{ $school['commune'] ?? '' }}</span></span>
                    </div>
                    <!-- Ecole -->
                    <div class="mb-2">
                        <span class="font-bold">ECOLE : </span>
                        <span class="dotted-line" style="width: 80%;"><span class="input-value">{{ $school['name'] ?? '' }}</span></span>
                    </div>
                    <!-- Code -->
                    <div>
                        <span class="font-bold">CODE: </span>
                        @for($i = 0; $i < 10; $i++)
                        <span class="square-box">{{ $schoolCodeArray[$i] ?? '' }}</span>
                        @endfor
                    </div>
                </td>
                <td style="width: 50%;">
                    <!-- Classe -->
                    <div class="mb-2">
                        <span class="font-bold">CLASSE : </span>
                        <span class="dotted-line" style="width: 80%;"><span class="input-value">{{ $classe['name'] ?? '' }}</span></span>
                    </div>
                    <!-- Eleve -->
                    <div class="mb-2">
                        <span class="font-bold">ELEVE : </span>
                        <span class="dotted-line" style="width: 80%;"><span class="input-value">{{ $student['full_name'] ?? '' }}</span></span>
                    </div>
                    <!-- Sexe -->
                    <div class="mb-2">
                        <span class="font-bold">SEXE : </span>
                        <span class="dotted-line" style="width: 30px; text-align: center;"><span class="input-value">{{ $student['gender'] ?? '' }}</span></span>
                    </div>
                    <!-- Naissance -->
                    <div class="mb-2">
                        <span class="font-bold">Né(e) à : </span>
                        <span class="dotted-line" style="width: 30%;"><span class="input-value">{{ $student['birth_place'] ?? '' }}</span></span>
                        <span class="font-bold"> LE : </span>
                        <span class="dotted-line" style="width: 30%;"><span class="input-value">{{ $student['birth_date'] ?? '' }}</span></span>
                    </div>
                    <!-- Perm ID -->
                    <div>
                        <span class="font-bold">N° PERM: </span>
                        @for($i = 0; $i < 14; $i++)
                        <span class="square-box">{{ $permIdArray[$i] ?? '' }}</span>
                        @endfor
                    </div>
                </td>
            </tr>
        </table>

        <!-- Bulletin Title -->
        <div class="bordered bg-gray-200 text-center p-1 mb-2 font-bold uppercase">
            BULLETIN DE LA {{ $classe['level'] ?? '' }} {{ $classe['option_type'] ?? '' }} / {{ $classe['option'] ?? '' }} <br />
            ANNEE SCOLAIRE {{ $schoolYear ?? '' }}
        </div>

        <!-- Grades Table -->
        <table class="bordered mb-2" style="font-size: 9px;">
            <thead>
                <tr class="bg-gray-200 font-bold">
                    <th class="p-05" rowspan="3" style="width: 25%;">BRANCHES</th>
                    <th class="p-05" colspan="4">PREMIER SEMESTRE</th>
                    <th class="p-05" colspan="4">DEUXIEME SEMESTRE</th>
                    <th class="p-05" rowspan="3" style="width: 30px;">TG</th>
                    <th class="p-05 bg-black" rowspan="3" style="width: 5px;"></th>
                    <th class="p-05" colspan="2" style="width: 60px;">Trav Journal</th>
                </tr>
                <tr class="bg-gray-200 font-bold">
                    <th class="p-05" colspan="2">Trav Journal</th>
                    <th class="p-05" rowspan="2">Exam</th>
                    <th class="p-05" rowspan="2">TOT</th>
                    <th class="p-05" colspan="2">Trav Journal</th>
                    <th class="p-05" rowspan="2">Exam</th>
                    <th class="p-05" rowspan="2">TOT</th>
                    <th class="p-05" rowspan="2">%</th>
                    <th class="p-05" rowspan="2">Sign prof</th>
                </tr>
                <tr class="bg-gray-200 font-bold">
                    <th class="p-05">1ère P</th>
                    <th class="p-05">2ème P</th>
                    <th class="p-05">3ème P</th>
                    <th class="p-05">4ème P</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupedGrades as $totalMax => $group)
                <tr class="bg-gray-300 font-bold">
                    <td class="p-05 text-left uppercase pl-1">MAXIMA</td>
                    @for($i = 1; $i <= 9; $i++)
                    <td class="p-05 text-center">
                        {{ $fmt($getGroupMaxima($group[0] ?? null, $getPeriodKey($i))) }}
                    </td>
                    @endfor
                    @if(($variant ?? 'standard') === 'state-exam')
                    <td colspan="4" class="bg-black"></td>
                    @endif
                    <td class="bg-black"></td>
                    <td colspan="2" class="bg-black"></td>
                </tr>

                @foreach($group as $grade)
                <tr>
                    <td class="text-left p-05 pl-1">{{ $grade['course'] ?? '' }}</td>
                    <td class="text-center p-05">{{ $fmt(isset($grade['note']['P1']) ? $grade['note']['P1'] : null) }}</td>
                    <td class="text-center p-05">{{ $fmt(isset($grade['note']['P2']) ? $grade['note']['P2'] : null) }}</td>
                    <td class="text-center p-05">{{ $fmt(isset($grade['note']['E1']) ? $grade['note']['E1'] : null) }}</td>
                    <td class="text-center p-05 bg-gray-100 font-bold">{{ isset($grade['sem1_total']) ? $fmt($grade['sem1_total']) : '-' }}</td>
                    <td class="text-center p-05">{{ $fmt(isset($grade['note']['P3']) ? $grade['note']['P3'] : null) }}</td>
                    <td class="text-center p-05">{{ $fmt(isset($grade['note']['P4']) ? $grade['note']['P4'] : null) }}</td>
                    <td class="text-center p-05">{{ $fmt(isset($grade['note']['E2']) ? $grade['note']['E2'] : null) }}</td>
                    <td class="text-center p-05 bg-gray-100 font-bold">{{ isset($grade['sem2_total']) ? $fmt($grade['sem2_total']) : '-' }}</td>
                    <td class="text-center p-05 bg-gray-200 font-bold">{{ $fmt(isset($grade['total_obtained']) ? $grade['total_obtained'] : null) }}</td>

                    @if(($variant ?? 'standard') === 'state-exam')
                    <td class="text-center p-05 font-bold">%</td>
                    <td class="text-center p-05 font-bold">{{ isset($grade['average_percent']) ? $fmt($grade['average_percent']) : '' }}</td>
                    <td></td>
                    @else
                    <td class="bg-black"></td>
                    <td class="text-center p-05 font-bold">{{ isset($grade['average_percent']) ? $fmt($grade['average_percent']) : '' }}</td>
                    <td colspan="2"></td>
                    @endif
                </tr>
                @endforeach
                @endforeach

                <!-- Maxima Généraux -->
                <tr class="font-bold bg-gray-200">
                    <td class="text-left p-05 pl-1">MAXIMA GENERAUX</td>
                    @for($i = 1; $i <= 9; $i++)
                    <td class="text-center p-05">
                        {{ $fmt($generalTotals['maxima'][$getPeriodKey($i)] ?? 0) }}
                    </td>
                    @endfor
                    <td class="bg-black" colspan="3"></td>
                </tr>

                <!-- Totaux Généraux -->
                <tr class="font-bold bg-gray-100">
                    <td class="text-left p-05 pl-1">TOTAUX</td>
                    @for($i = 1; $i <= 9; $i++)
                    <td class="text-center p-05">
                        {{ $fmt($generalTotals['obtained'][$getNoteKey($i)] ?? 0) }}
                    </td>
                    @endfor
                    <td class="bg-black" colspan="3"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-left p-05 pl-1 font-bold">POURCENTAGE</td>
                    @for($i = 1; $i <= 9; $i++)
                    <td class="text-center p-05">
                        @php
                            $key = $getNoteKey($i);
                            if (in_array($key, ['S1', 'S2'])) {
                                echo isset($summary['semester_place'][$key]['percent']) ? $summary['semester_place'][$key]['percent'] . '%' : '';
                            } elseif ($key === 'TG') {
                                echo isset($summary['overall_percent']) ? $summary['overall_percent'] . '%' : '';
                            } else {
                                echo $getPeriodPercent($key);
                            }
                        @endphp
                    </td>
                    @endfor
                    <td class="bg-black"></td>
                    <td class="text-left p-05" rowspan="5" colspan="2" style="vertical-align: top;">
                        <div style="padding: 5px;">
                            <div class="mb-2">
                                PASSE (1) @if($elevePasseClasse)<span style="float:right; font-size: 14px;">✓</span>@endif
                            </div>
                            <div class="mb-2">
                                DOUBLE (1) @if(!$elevePasseClasse)<span style="float:right; font-size: 14px;">✓</span>@endif
                            </div>
                            <div class="mt-2 mb-2">Le {{ $dateImpression }}</div>
                            <div>Le Chef d'établissement</div>
                            <div style="font-size: 8px; color: grey; margin-top: 5px;">Sceau de l'école</div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="text-left p-05 pl-1 font-bold">PLACE / NBRE D'ELEVES</td>
                    @for($i = 1; $i <= 9; $i++)
                    <td class="text-center p-05">
                        @php
                            $key = $getNoteKey($i);
                            $placeTxt = '';
                            if (in_array($key, ['S1', 'S2'])) {
                                $placeTxt = $summary['semester_place'][$key]['label'] ?? '';
                            } elseif ($key === 'TG') {
                                $placeTxt = $summary['place'] ?? '';
                            } else {
                                $placeTxt = $summary['period_exam_place'][$key]['label'] ?? '';
                            }
                        @endphp
                        {{ $placeTxt }}
                    </td>
                    @endfor
                    <td class="bg-black"></td>
                </tr>

                <tr>
                    <td class="text-left p-05 pl-1 font-bold">APPLICATION</td>
                    @for($i = 1; $i <= 2; $i++)
                    <td></td>
                    @endfor
                    <td class="bg-gray-200" colspan="2" rowspan="2"></td>
                    @for($i = 1; $i <= 2; $i++)
                    <td></td>
                    @endfor
                    <td class="bg-gray-200" colspan="3" rowspan="2"></td>
                    <td class="bg-black"></td>
                </tr>

                <tr>
                    <td class="text-left p-05 pl-1 font-bold">CONDUITE</td>
                    @for($i = 1; $i <= 2; $i++)
                    <td></td>
                    @endfor
                    @for($i = 1; $i <= 2; $i++)
                    <td></td>
                    @endfor
                    <td class="bg-black"></td>
                </tr>

                <tr>
                    <td class="text-left p-05 pl-1 font-bold">Signat. du resp.</td>
                    @for($i = 1; $i <= 2; $i++)
                    <td></td>
                    @endfor
                    <td class="bg-gray-100" colspan="2"></td>
                    @for($i = 1; $i <= 3; $i++)
                    <td></td>
                    @endfor
                    <td class="bg-gray-100" colspan="2"></td>
                    <td class="bg-black"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <table class="bordered w-full p-1" style="font-size: 10px;">
            <tr>
                <td style="width: 33%; vertical-align: bottom; padding: 5px;">
                    <div @if(!$elevePasse) style="text-decoration: line-through; opacity: 0.5" @endif>
                        - L'élève passe dans la classe supérieure (1)
                    </div>
                    <div @if($elevePasse) style="text-decoration: line-through; opacity: 0.5" @endif>
                        - L'élève double la classe (1)
                    </div>
                    <div style="margin-top: 15px;"><strong>Signature de l'élève</strong></div>
                </td>
                <td style="width: 33%; vertical-align: bottom; text-align: center; padding: 5px;">
                    <strong>Sceau de l'Ecole</strong>
                </td>
                <td style="width: 33%; vertical-align: bottom; text-align: center; padding: 5px;">
                    Fait à <span class="uppercase font-bold">{{ $school['city'] ?? '................' }}</span>, le
                    <span class="dotted-line" style="width: 80px; text-align: center; display: inline-block;">{{ $dateImpression }}</span><br /><br />
                    <strong>Chef d'Établissement</strong><br />
                    (Noms & Signature)
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
