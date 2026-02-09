<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Services\Accounts\AccountTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class PaymentController extends Controller
{
    public function index(): JsonResponse
    {
        $payments = Payment::with([
            'student',
            'classroom',
            'schoolYear',
            'fee',
            'paymentMethod',
            'currency',
        ])->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Récupérer élève & contexte
            $student = Student::findOrFail($data['student_id']);

            // Essayer d'extraire la classe et l'année à partir de l'inscription la plus récente
            $registration = Registration::where('student_id', $student->id)
                ->orderByDesc('id')
                ->first();

            $data['classroom_id'] = $registration->classroom_id ?? null;

            // Normaliser l'ID d'école avant d'appeler SchoolYear::active
            $schoolId = $student->school_id;
            if (is_string($schoolId)) {
                $schoolId = mb_trim($schoolId);
                $schoolId = ctype_digit($schoolId) ? (int) $schoolId : null;
            } elseif (! is_int($schoolId)) {
                $schoolId = null;
            }

            $data['school_year_id'] = $registration->school_year_id
                ?? optional(SchoolYear::active($schoolId))->id;

            // Créer le paiement
            $payment = Payment::create($data);
            // Créer la transaction associée sur le compte de destination
            AccountTransactionService::handleCreate($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistré avec succès.',
                'data' => $payment->load([
                    'student', 'classroom', 'schoolYear', 'fee', 'paymentMethod', 'currency',
                ]),
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du paiement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $payment = Payment::with(['student', 'classroom', 'schoolYear', 'fee', 'paymentMethod', 'currency'])->find($id);

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Paiement non trouvé.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Détails du paiement',
            'data' => $payment,
        ], 200);
    }

    public function update(UpdatePaymentRequest $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $payment = Payment::find($id);

            if (! $payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement non trouvé.',
                ], 404);
            }

            $data = $request->validated();

            // Si l'élève change, recalculer la classe et l'année
            if (array_key_exists('student_id', $data)) {
                $student = Student::findOrFail($data['student_id']);
                $registration = Registration::where('student_id', $student->id)
                    ->orderByDesc('id')
                    ->first();
                $data['classroom_id'] = $registration->classroom_id ?? null;

                // Normaliser l'ID d'école avant d'appeler SchoolYear::active
                $schoolId = $student->school_id;
                if (is_string($schoolId)) {
                    $schoolId = mb_trim($schoolId);
                    $schoolId = ctype_digit($schoolId) ? (int) $schoolId : null;
                } elseif (! is_int($schoolId)) {
                    $schoolId = null;
                }

                $data['school_year_id'] = $registration->school_year_id
                    ?? optional(SchoolYear::active($schoolId))->id;
            }

            $payment->update($data);

            AccountTransactionService::handleUpdate($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement mis à jour avec succès.',
                'data' => $payment->refresh()->load([
                    'student', 'classroom', 'schoolYear', 'fee', 'paymentMethod', 'currency',
                ]),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du paiement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $payment = Payment::find($id);

            if (! $payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement non trouvé.',
                ], 404);
            }

            $payment->delete();

            AccountTransactionService::handleDelete($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Paiement supprimé avec succès.',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du paiement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
