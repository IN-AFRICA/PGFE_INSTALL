<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class StudentTransferController extends Controller
{
    /**
     * Liste le parcours (transferts) d'un élève.
     * Accepte soit l'ID numérique, soit le UUID de l'élève.
     */
    public function index(Request $request, string $student): JsonResponse
    {
        $studentModel = Student::query()
            ->when(is_numeric($student), function ($q) use ($student) {
                $q->where('id', (int) $student);
            })
            ->orWhere('uuid', $student)
            ->first();

        if (! $studentModel) {
            return response()->json([
                'message' => "Cet élève n'existe pas.",
            ], 404);
        }

        $transfers = StudentTransfer::with(['fromSchool', 'toSchool', 'fromClassroom', 'toClassroom', 'schoolYear'])
            ->where('student_id', $studentModel->id)
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'student' => [
                'id' => $studentModel->id,
                'uuid' => $studentModel->uuid,
            ],
            'data' => $transfers,
        ]);
    }

    /**
     * Crée un transfert d'école pour un élève et met à jour son school_id.
     * Accepte soit l'ID numérique, soit le UUID de l'élève.
     */
    public function store(Request $request, string $student): JsonResponse
    {
        $user = $request->user();

        $studentModel = Student::query()
            ->when(is_numeric($student), function ($q) use ($student) {
                $q->where('id', (int) $student);
            })
            ->orWhere('uuid', $student)
            ->first();

        if (! $studentModel) {
            return response()->json([
                'message' => "Cet élève n'existe pas.",
            ], 404);
        }

        $fromSchoolId = (int) $studentModel->school_id;

        $data = $request->validate([
            'to_school_id' => 'required|integer|exists:schools,id|different:from_school_id',
            'from_classroom_id' => 'nullable|integer|exists:classrooms,id',
            'to_classroom_id' => 'nullable|integer|exists:classrooms,id',
            'school_year_id' => 'nullable|integer|exists:school_years,id',
            'transfer_date' => 'nullable|date',
            'reason' => 'nullable|string',
        ]);

        // Remplacer la contrainte symbolique different:from_school_id
        if ($data['to_school_id'] === $fromSchoolId) {
            return response()->json([
                'message' => 'La nouvelle école doit être différente de l\'école actuelle.',
            ], 422);
        }

        $transfer = DB::transaction(function () use ($data, $studentModel, $fromSchoolId, $user) {
            $transfer = StudentTransfer::create([
            'student_id' => $studentModel->id,
                'from_school_id' => $fromSchoolId,
                'to_school_id' => $data['to_school_id'],
                'from_classroom_id' => $data['from_classroom_id'] ?? null,
                'to_classroom_id' => $data['to_classroom_id'] ?? null,
                'school_year_id' => $data['school_year_id'] ?? null,
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'created_by' => $user?->id,
            ]);

            // Mettre à jour l'école de l'élève pour refléter le transfert
            $studentModel->update([
                'school_id' => $data['to_school_id'],
            ]);

            return $transfer->load(['fromSchool', 'toSchool', 'fromClassroom', 'toClassroom', 'schoolYear']);
        });

        return response()->json([
            'message' => 'Transfert effectué avec succès.',
            'data' => $transfer,
        ], 201);
    }
}
