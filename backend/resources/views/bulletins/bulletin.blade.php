<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin Scolaire - {{ $student['full_name'] }}</title>

    <!-- Tailwind CSS via CDN pour le PDF -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Mulish', 'sans-serif'],
                    },
                    colors: {
                        primary: '#5d2b90',
                    }
                }
            }
        }
    </script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Mulish', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table-bordered,
        .table-bordered th,
        .table-bordered td {
            border: 1px solid black;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: white;
            }

            .shadow-lg {
                box-shadow: none !important;
                margin: 0;
                padding: 0;
            }

            #app {
                box-shadow: none;
                margin: 0;
                width: 100%;
                max-width: none;
            }
        }

        .dotted-input {
            border-bottom: 2px dotted #000;
        }

        /* Helper function replacements */
        <?php
            $fmt = function($val) {
                if ($val === null || $val === '') return '';
                $n = is_numeric($val) ? floatval($val) : 0;
                return number_format($n, 1, '.', '');
            };

            $dateImpression = date('d/m/Y');

            // Calculer les totaux généraux
            $generalTotals = isset($summary['period_exam_totals']) ? $summary['period_exam_totals'] : [
                'maxima' => ['P1' => 140, 'P2' => 140, 'E1' => 280, 'TOT1' => 560, 'P3' => 140, 'P4' => 140, 'E2' => 280, 'TOT2' => 560, 'TG' => 1120],
                'obtained' => ['P1' => 112, 'P2' => 108, 'E1' => 224, 'S1' => 440, 'P3' => 115, 'P4' => 110, 'E2' => 220, 'S2' => 445, 'TG' => 885]
            ];

            // Grouper les notes par total_maxima
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

            $elevePasseClasse = true;
            $elevePasse = true;

            // Helper pour split string en array
            $schoolCodeArray = isset($school['code']) ? str_split($school['code']) : [];
            $permIdArray = isset($student['matricule']) ? str_split($student['matricule']) : [];
        ?>
    </style>
</head>

<body class="bg-gray-100 p-8 print:p-0">
    <div id="app" class="max-w-[210mm] mx-auto bg-white shadow-lg p-8 min-h-[297mm] print:w-full print:max-w-none">

        <!-- Header Section -->
        <div class="mb-2">
            <div class="flex justify-between items-center text-center font-bold leading-tight border border-black p-2 mb-2">
                <img alt="Emblème RDC" class="w-14 h-auto object-contain" src="https://upload.wikimedia.org/wikipedia/commons/6/6f/Flag_of_the_Democratic_Republic_of_the_Congo.svg" />
                <div class="flex-grow px-2">
                    <div class="text-[12px]">REPUBLIQUE DEMOCRATIQUE DU CONGO</div>
                    <div class="text-[10px]">MINISTERE DE L'EDUCATION NATIONALE ET NOUVELLE CITOYENNETE</div>
                </div>
                <img alt="Emblème Ministère" class="w-14 h-auto object-contain" src="https://upload.wikimedia.org/wikipedia/commons/1/1b/Coat_of_arms_of_the_Democratic_Republic_of_the_Congo.svg" />
            </div>

            <div class="border border-black p-1 text-[10px] mb-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold">N° ID.</span>
                    <div class="flex gap-0.5">
                        @for($i = 1; $i <= 20; $i++)
                        <div class="w-5 h-5 border border-black"></div>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[10px]">
                <div class="border border-black p-2 space-y-1">
                    <!-- Dotted Input pour PROVINCE EDUC. -->
                    <div class="flex items-center mb-1">
                        <span class="mr-2 whitespace-nowrap font-bold text-[10px]">PROVINCE EDUC.</span>
                        <div class="flex-grow border-b border-dotted border-black min-h-[1.2rem] px-2 font-mono text-sm leading-none pt-1 uppercase">
                            {{ $school['province'] ?? '' }}
                        </div>
                    </div>

                    <!-- Dotted Input pour VILLE -->
                    <div class="flex items-center mb-1">
                        <span class="mr-2 whitespace-nowrap font-bold text-[10px]">VILLE</span>
                        <div class="flex-grow border-b border-dotted border-black min-h-[1.2rem] px-2 font-mono text-sm leading-none pt-1 uppercase">
                            {{ $school['city'] ?? '' }}
                        </div>
                    </div>

                    <!-- Dotted Input pour COMMUNE / TER. -->
                    <div class="flex items-center mb-1">
                        <span class="mr-2 whitespace-nowrap font-bold text-[10px]">COMMUNE / TER.</span>
                        <div class="flex-grow border-b border-dotted border-black min-h-[1.2rem] px-2 font-mono text-sm leading-none pt-1 uppercase">
                            {{ $school['commune'] ?? '' }}
                        </div>
                    </div>

                    <!-- Dotted Input pour ECOLE -->
                    <div class="flex items-center mb-1">
                        <span class="mr-2 whitespace-nowrap font-bold text-[10px]">ECOLE</span>
                        <div class="flex-grow border-b border-dotted border-black min-h-[1.2rem] px-2 font-mono text-sm leading-none pt-1 uppercase">
                            {{ $school['name'] ?? '' }}
                        </div>
                    </div>

                    <!-- Number Input Grid pour CODE -->
                    <div class="mt-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[10px] mr-2">CODE:</span>
                            <div class="flex gap-0.5">
                                @for($i = 0; $i < 10; $i++)
                                <div class="w-5 h-5 flex items-center justify-center border border-black text-[10px] font-bold">
                                    {{ $schoolCodeArray[$i] ?? '' }}
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-black p-2 space-y-1">
                    <!-- Dotted Input pour CLASSE -->
                    <div class="flex items-center mb-1">
                        <span class="mr-2 whitespace-nowrap font-bold text-[10px]">CLASSE</span>
                        <div class="flex-grow border-b border-dotted border-black min-h-[1.2rem] px-2 font-mono text-sm leading-none pt-1 uppercase">
                            {{ $classe['name'] ?? '' }}
                        </div>
                    </div>

                    <!-- Eleve et Sexe -->
                    <div class="flex items-center">
                        <div class="flex-grow flex items-center">
                            <span class="mr-2 font-bold text-[10px]">ELEVE:</span>
                            <div class="flex-grow border-b border-dotted border-black uppercase text-sm font-bold">{{ $student['full_name'] ?? '' }}</div>
                        </div>
                        <div class="ml-2 flex items-center">
                            <span class="mr-1 font-bold text-[10px]">SEXE:</span>
                            <span class="border-b border-dotted border-black w-6 text-center">{{ $student['gender'] ?? '' }}</span>
                        </div>
                    </div>

                    <!-- Lieu et date de naissance -->
                    <div class="flex items-center text-[10px] mt-1">
                        <span class="mr-1 font-bold whitespace-nowrap">Né(e) à:</span>
                        <span class="flex-grow border-b border-dotted border-black mr-1 uppercase">{{ $student['birth_place'] ?? '' }}</span>
                        <span class="mr-1 font-bold">LE:</span>
                        <span class="w-20 border-b border-dotted border-black text-center">{{ $student['birth_date'] ?? '' }}</span>
                    </div>

                    <!-- Number Input Grid pour N° PERM -->
                    <div class="mt-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[10px] mr-2">N° PERM:</span>
                            <div class="flex gap-0.5">
                                @for($i = 0; $i < 14; $i++)
                                <div class="w-5 h-5 flex items-center justify-center border border-black text-[10px] font-bold">
                                    {{ $permIdArray[$i] ?? '' }}
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Titre Bulletin -->
        <div class="text-center bg-gray-200 bg-opacity-50 p-2 relative border border-black my-4">
            <h3 class="font-bold uppercase text-sm">
                BULLETIN DE LA {{ $classe['level'] ?? '' }} {{ $classe['option_type'] ?? '' }} / {{ $classe['option'] ?? '' }} <br />
                ANNEE SCOLAIRE {{ $schoolYear ?? '' }}
            </h3>
        </div>

        <!-- Tableau des Notes -->
        <div class="w-full flex flex-col justify-start items-center">
            <table class="border-collapse w-full bg-white text-[10px] text-center">
                <thead class="font-bold bg-gray-200">
                    <tr>
                        <th class="p-0.5 align-middle border border-black" rowspan="3">BRANCHES</th>
                        <th class="p-0.5 border border-black" colspan="4">PREMIER SEMESTRE</th>
                        <th class="p-0.5 border border-black" colspan="4">DEUXIEME SEMESTRE</th>
                        <th class="p-0.5 w-8 align-middle border border-black" rowspan="3">TG</th>
                        <th class="p-1 bg-black align-middle border border-black" rowspan="3"></th>
                        <th class="p-0.5 w-16 border border-black" colspan="2">Trav Journal</th>
                    </tr>
                    <tr>
                        <th class="p-0.5 border border-black" colspan="2">Trav Journal</th>
                        <th class="p-0.5 w-8 align-middle border border-black" rowspan="2">Exam</th>
                        <th class="p-0.5 w-8 align-middle border border-black" rowspan="2">TOT</th>
                        <th class="p-0.5 border border-black" colspan="2">Trav Journal</th>
                        <th class="p-0.5 w-8 align-middle border border-black" rowspan="2">Exam</th>
                        <th class="p-0.5 w-8 align-middle border border-black" rowspan="2">TOT</th>
                        <th class="p-0.5 w-8 align-middle border border-black" rowspan="2">%</th>
                        <th class="p-0.5 w-32 align-middle border border-black" rowspan="2">Sign prof</th>
                    </tr>
                    <tr>
                        <th class="w-8 border border-black">1ère P</th>
                        <th class="w-8 border border-black">2ème P</th>
                        <th class="w-8 border border-black">3ème P</th>
                        <th class="w-8 border border-black">4ème P</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Groupes de notes -->
                    @foreach($groupedGrades as $totalMax => $group)
                    <tr class="bg-gray-300 font-bold border border-black">
                        <th class="p-0.5 text-left uppercase border border-black pl-1">MAXIMA</th>
                        @for($i = 1; $i <= 9; $i++)
                        <td class="p-0.5 border border-black">
                            {{ $fmt($getGroupMaxima($group[0] ?? null, $getPeriodKey($i))) }}
                        </td>
                        @endfor
                        @if(($variant ?? 'standard') === 'state-exam')
                        <td colspan="4" class="bg-black border border-black"></td>
                        @endif
                        <td class="p-0.5 bg-black border border-black"></td>
                        <td class="bg-black border border-black" colspan="2"></td>
                    </tr>

                    @foreach($group as $grade)
                    <tr class="border border-black">
                        <td class="text-left p-0.5 pl-1 font-medium border border-black truncate max-w-[150px]">{{ $grade['course'] ?? '' }}</td>
                        <td class="p-0.5 text-center border border-black">{{ $fmt($grade['note']['P1'] ?? '') }}</td>
                        <td class="p-0.5 text-center border border-black">{{ $fmt($grade['note']['P2'] ?? '') }}</td>
                        <td class="p-0.5 text-center border border-black">{{ $fmt($grade['note']['E1'] ?? '') }}</td>
                        <td class="p-0.5 bg-gray-100 font-bold text-center border border-black">{{ isset($grade['sem1_total']) ? $fmt($grade['sem1_total']) : '-' }}</td>
                        <td class="p-0.5 text-center border border-black">{{ $fmt($grade['note']['P3'] ?? '') }}</td>
                        <td class="p-0.5 text-center border border-black">{{ $fmt($grade['note']['P4'] ?? '') }}</td>
                        <td class="p-0.5 text-center border border-black">{{ $fmt($grade['note']['E2'] ?? '') }}</td>
                        <td class="p-0.5 bg-gray-100 font-bold text-center border border-black">{{ isset($grade['sem2_total']) ? $fmt($grade['sem2_total']) : '-' }}</td>
                        <td class="p-0.5 bg-gray-200 font-bold text-center border border-black">{{ $fmt($grade['total_obtained'] ?? '') }}</td>

                        @if(($variant ?? 'standard') === 'state-exam')
                        <td class="p-0.5 text-[8px] font-bold border border-black">%</td>
                        <td class="p-0.5 font-bold text-center border border-black">{{ isset($grade['average_percent']) ? $fmt($grade['average_percent']) : '' }}</td>
                        <td class="p-0.5 border border-black"></td>
                        @else
                        <td class="p-0.5 bg-black border border-black"></td>
                        <td class="p-0.5 font-bold text-center border border-black">{{ isset($grade['average_percent']) ? $fmt($grade['average_percent']) : '' }}</td>
                        <td colspan="2" class="border border-black"></td>
                        @endif
                    </tr>
                    @endforeach
                    @endforeach

                    <!-- Maxima Généraux -->
                    <tr class="font-bold bg-gray-200 border border-black">
                        <td class="text-left p-0.5 pl-1 border border-black">MAXIMA GENERAUX</td>
                        @for($i = 1; $i <= 9; $i++)
                        <td class="p-0.5 text-center border border-black">
                            {{ $fmt($generalTotals['maxima'][$getPeriodKey($i)] ?? 0) }}
                        </td>
                        @endfor
                        <td class="bg-black border border-black" colspan="3"></td>
                    </tr>

                    <!-- Totaux Généraux -->
                    <tr class="font-bold bg-gray-100 border border-black">
                        <td class="text-left p-0.5 pl-1 border border-black">TOTAUX</td>
                        @for($i = 1; $i <= 9; $i++)
                        <td class="p-0.5 text-center border border-black">
                            {{ $fmt($generalTotals['obtained'][$getNoteKey($i)] ?? 0) }}
                        </td>
                        @endfor
                        <td class="bg-black border border-black" colspan="3"></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="text-left p-0.5 pl-1 border border-black font-bold">POURCENTAGE</td>
                        @for($i = 1; $i <= 9; $i++)
                        <td class="p-0.5 text-center border border-black">
                            {{ $getPeriodPercent($getNoteKey($i)) }}
                        </td>
                        @endfor
                        <td class="p-0.5 bg-black border border-black"></td>
                        <td class="p-0.5 text-start align-top border border-black" rowspan="5" colspan="2">
                            <div class="px-2 pt-2 text-[10px] font-bold">
                                <div class="mb-2">
                                    PASSE (1) @if($elevePasseClasse)<span class="float-right text-lg">✓</span>@endif
                                </div>
                                <div class="mb-4">
                                    DOUBLE (1) @if(!$elevePasseClasse)<span class="float-right text-lg">✓</span>@endif
                                </div>
                                <div class="mt-4 mb-1">Le {{ $dateImpression }}</div>
                                <div>Le Chef d'établissement</div>
                                <div class="text-[8px] uppercase mt-1 text-gray-500">Sceau de l'école</div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="text-left p-0.5 pl-1 border border-black font-bold">PLACE / NBRE D'ELEVES</td>
                        @for($i = 1; $i <= 9; $i++)
                        <td class="p-0.5 text-center border border-black">
                            {{ $getPeriodPlace($getNoteKey($i)) }}
                        </td>
                        @endfor
                        <td class="p-0.5 bg-black border border-black"></td>
                    </tr>

                    <tr>
                        <td class="text-left p-0.5 pl-1 border border-black font-bold">APPLICATION</td>
                        @for($i = 1; $i <= 2; $i++)
                        <td class="border border-black"></td>
                        @endfor
                        <td class="bg-gray-200 border border-black" colspan="2" rowspan="2"></td>
                        @for($i = 1; $i <= 2; $i++)
                        <td class="border border-black"></td>
                        @endfor
                        <td class="bg-gray-200 border border-black" colspan="3" rowspan="2"></td>
                        <td class="p-0.5 bg-black border border-black"></td>
                    </tr>

                    <tr>
                        <td class="text-left p-0.5 pl-1 border border-black font-bold">CONDUITE</td>
                        @for($i = 1; $i <= 2; $i++)
                        <td class="border border-black"></td>
                        @endfor
                        @for($i = 1; $i <= 2; $i++)
                        <td class="border border-black"></td>
                        @endfor
                        <td class="p-0.5 bg-black border border-black"></td>
                    </tr>

                    <tr>
                        <td class="text-left p-0.5 pl-1 border border-black font-bold">Signat. du resp.</td>
                        @for($i = 1; $i <= 2; $i++)
                        <td class="border border-black"></td>
                        @endfor
                        <td class="bg-gray-100 border border-black" colspan="2"></td>
                        @for($i = 1; $i <= 3; $i++)
                        <td class="border border-black"></td>
                        @endfor
                        <td class="bg-gray-100 border border-black" colspan="2"></td>
                        <td class="p-0.5 bg-black border border-black"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer -->
        <div class="mt-2 p-2 border border-black text-[10px]">
            <div class="flex justify-between items-end">
                <div class="text-left w-1/3">
                    <div @if(!$elevePasse) style="text-decoration: line-through; opacity: 0.5" @endif>
                        - L'élève passe dans la classe supérieure (1)
                    </div>
                    <div @if($elevePasse) style="text-decoration: line-through; opacity: 0.5" @endif>
                        - L'élève double la classe (1)
                    </div>
                    <div class="mt-4"><strong>Signature de l'élève</strong></div>
                </div>
                <div class="text-center w-1/3">
                    <strong>Sceau de l'Ecole</strong>
                </div>
                <div class="text-center w-1/3">
                    Fait à <span class="uppercase font-bold">{{ $school['city'] ?? '' }}</span>, le
                    <span class="border-b border-dotted border-black inline-block min-w-[3cm]">{{ $dateImpression }}</span><br /><br />
                    <strong>Chef d'Établissement</strong><br />
                    (Noms & Signature)
                </div>
            </div>
        </div>
    </div>
</body>
</html>
