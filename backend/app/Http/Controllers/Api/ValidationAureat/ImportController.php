<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\ValidationAureat;

use App\Http\Controllers\Controller;
use App\Imports\PassThroughArrayImport;
use App\Models\ValidationAureat;
use App\Services\StudentFinder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

final class ImportController extends Controller
{
    /**
     * Handle the import of validation aureats from an Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        DB::beginTransaction();
        try {
            $rows = Excel::toArray(new PassThroughArrayImport(), $file)[0];
            if (empty($rows)) {
                return response()->json([]);
            }

            // Détecter les entêtes pour localiser les colonnes clés
            $header = $rows[0] ?? [];
            $normalized = array_map(function ($h) {
                return is_string($h) ? mb_trim(mb_strtolower($h)) : $h;
            }, $header);

            // Helper: trouve l'index d'une colonne via alias connus
            $findIndex = function (array $aliases) use ($normalized) {
                foreach ($aliases as $alias) {
                    $alias = mb_trim(mb_strtolower($alias));
                    $idx = array_search($alias, $normalized, true);
                    if ($idx !== false) {
                        return $idx;
                    }
                }

                return null;
            };
            $getVal = function (array $row, ?int $idx, ?int $fallback = null) {
                if ($idx !== null && array_key_exists($idx, $row)) {
                    return $row[$idx];
                }
                if ($fallback !== null && array_key_exists($fallback, $row)) {
                    return $row[$fallback];
                }

                return null;
            };

            // Indices par entêtes (fallback sur index historiques si manquants)
            $idxLast = $findIndex(['last_name', 'nom']);
            $idxMiddle = $findIndex(['middle_name', 'postnom', 'second_name']);
            $idxFirst = $findIndex(['first_name', 'prenom', 'prénom']);
            $idxReg = $findIndex(['registration_number', 'matricule', 'registration', 'reg_number']);
            $idxGender = $findIndex(['gender', 'sexe']);
            $idxDept = $findIndex(['department', 'filiere', 'département', 'departement']);
            $idxClass = $findIndex(['class', 'classe']);
            $idxYear = $findIndex(['year', 'annee', 'année']);
            $idxCycle = $findIndex(['cycle']);
            $idxPresent = $findIndex(['present']); // optionnel
            $idxComment = $findIndex(['comment', 'remarque', 'observations']);
            $idxPercent = $findIndex(['percentage', 'pourcentage', '%']);

            $errors = [];
            $imported = [];
            $resolved = 0;
            $duplicatesSkipped = 0;
            $seen = [];
            // Skip header row
            foreach (array_slice($rows, 1, null, true) as $idx => $row) {
                $line = (int) $idx + 2; // +2 pour compter l'entête
                // Lire champs de base
                $lastName = (string) ($getVal($row, $idxLast, 0) ?? '');
                $firstName = (string) ($getVal($row, $idxFirst, 2) ?? '');
                if (mb_trim($lastName) === '' || mb_trim($firstName) === '') {
                    // On ignore les lignes sans nom/prénom
                    continue;
                }
                $middleName = $getVal($row, $idxMiddle, 1);

                // Gender (normalisation)
                $genderRaw = (string) ($getVal($row, $idxGender, 4) ?? '');
                $g = mb_strtolower(mb_trim($genderRaw));
                if ($g === 'm' || $g === 'masculin' || $g === 'male') {
                    $gender = 'male';
                } elseif ($g === 'f' || $g === 'feminin' || $g === 'féminin' || $g === 'female') {
                    $gender = 'female';
                } elseif ($g === '') {
                    $gender = null;
                } else {
                    $gender = 'other';
                }

                $department = $getVal($row, $idxDept, 5);
                $className = (string) ($getVal($row, $idxClass, 6) ?? '');
                $year = $getVal($row, $idxYear, 7);
                $cycle = $getVal($row, $idxCycle, 8);

                // Pourcentage
                $percentage = null;
                $percentRaw = $getVal($row, $idxPercent, null);
                if ($percentRaw !== null) {
                    $raw = mb_trim((string) $percentRaw);
                    if ($raw !== '') {
                        $raw = str_replace(['%', ' '], '', $raw);
                        $raw = str_replace(',', '.', $raw);
                        $val = is_numeric($raw) ? (float) $raw : null;
                        if ($val !== null) {
                            $val = max(0.0, min(100.0, $val));
                            $percentage = (int) round($val);
                        }
                    }
                }

                // Commentaire
                $comment = $getVal($row, $idxComment, 10);

                // Present (optionnel)
                $present = false;
                $presentRaw = $getVal($row, $idxPresent, null);
                if ($presentRaw !== null) {
                    $present = (bool) $presentRaw;
                }

                // 1) si fourni, utiliser tel quel
                $regNumber = $getVal($row, $idxReg, null);

                // 2) sinon, tenter de déduire via StudentFinder (classe + filière + nom + genre)
                if (empty($regNumber)) {
                    // On n'arrête pas l'import si la classe est manquante; on tente quand même et on marquera l'erreur ensuite si besoin
                    $regId = StudentFinder::findRegistrationId(
                        $lastName,
                        $firstName,
                        $middleName !== null ? (string) $middleName : null,
                        $className !== '' ? $className : null,
                        $department !== null ? (string) $department : null,
                        $gender
                    );
                    if ($regId !== null) {
                        $regNumber = (string) $regId;
                    } else {
                        $errors[] = [
                            'line' => $line,
                            'reason' => "Impossible de déduire le numéro d'inscription (Nom/Prénom + Classe + Filière)",
                            'last_name' => $lastName,
                            'middle_name' => $middleName,
                            'first_name' => $firstName,
                            'class' => $className,
                            'department' => $department,
                        ];
                    }
                }

                if (! empty($regNumber)) {
                    $resolved++;
                }

                // Déduplication: ne garder qu'une ligne par clé logique
                $keyParts = [];
                if (! empty($regNumber)) {
                    $keyParts[] = 'reg:'.mb_strtolower(mb_trim((string) $regNumber));
                } else {
                    $keyParts[] = 'ln:'.mb_strtolower(mb_trim($lastName));
                    $keyParts[] = 'fn:'.mb_strtolower(mb_trim($firstName));
                    $keyParts[] = 'mn:'.mb_strtolower(mb_trim((string) ($middleName ?? '')));
                    $keyParts[] = 'cl:'.mb_strtolower(mb_trim((string) $className));
                    $keyParts[] = 'dp:'.mb_strtolower(mb_trim((string) ($department ?? '')));
                }
                $logicalKey = implode('|', $keyParts);
                if (isset($seen[$logicalKey])) {
                    $duplicatesSkipped++;

                    continue; // ignorer doublon
                }
                $seen[$logicalKey] = true;

                $data = [
                    'last_name' => $lastName,
                    'middle_name' => $middleName,
                    'first_name' => $firstName,
                    'registration_number' => $regNumber ?: null,
                    'gender' => $gender,
                    'department' => $department,
                    'class' => $className ?: null,
                    'year' => $year,
                    'cycle' => $cycle,
                    'present' => $present,
                    'comment' => $comment,
                    'percentage' => $percentage,
                ];

                $imported[] = ValidationAureat::create($data);
            }

            // Import non-bloquant: commit même si des erreurs existent
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => empty($errors)
                    ? 'Import terminé avec succès'
                    : 'Import terminé: certaines lignes nécessitent une résolution manuelle',
                'imported_count' => count($imported),
                'resolved_count' => $resolved,
                'failed_count' => count($errors),
                'duplicates_skipped' => $duplicatesSkipped,
                'errors' => $errors,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }

        // unreachable
    }
}
