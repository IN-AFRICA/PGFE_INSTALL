<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMotifRequest;
use App\Models\PaymentMotif;
use Illuminate\Http\JsonResponse;    
use \Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaymentMotifsExport;



final class PaymentMotifController extends Controller
{

    public function index(): JsonResponse
    {
        $motifs = PaymentMotif::with('feeType')->get();

        return response()->json([
            'success' => true,
            'motifs' => $motifs,
        ]);
    }

    public function store(PaymentMotifRequest $request): JsonResponse
    {
        $motif = PaymentMotif::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Motif de paiement créé avec succès.',
            'motif' => $motif,
        ], 201);
    }

    public function show(PaymentMotif $paymentMotif): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Détails du motif de paiement',
            'motif' => $paymentMotif->load('feeType'),
        ], 200);
    }

    public function update(PaymentMotifRequest $request, PaymentMotif $paymentMotif): JsonResponse
    {
        $paymentMotif->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Motif de paiement mis à jour avec succès.',
            'motif' => $paymentMotif,
        ]);
    }

    public function destroy(PaymentMotif $paymentMotif): JsonResponse
    {
        $paymentMotif->delete();

        return response()->json([
            'success' => true,
            'message' => 'Motif de paiement supprimé avec succès.',
        ]);
    }
        public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PaymentMotifsExport, 'motifs_paiement.xlsx');
    }
}
