<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Filiaires;

use App\Exports\FiliairesExport;
use App\Http\Controllers\Controller;
use App\Models\Filiaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

final class ListsFiliaireController extends Controller
{
    /**
     * Export filiaires as Excel file.
     */
    public function export(Request $request)
    {
        $fileName = 'filiaires_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new FiliairesExport(), $fileName);
    }
    public function __invoke(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Vérifie qu’un utilisateur est bien connecté et qu’il a une école associée
        if (! $user || ! $user->school_id) {
            return response()->json([
                'data' => [],
                'message' => 'Aucune école associée à cet utilisateur.',
            ], Response::HTTP_FORBIDDEN);
        }

        $schoolId = (int) $user->school_id;

        // On récupère les filières directement associées à l'école de l'utilisateur

        $query = Filiaire::with('cycles.academicLevels')->where('school_id', $schoolId);
        if ($request->filled('search')) {
            $search = mb_strtolower(mb_trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"]);
            });
        }
        $filiaires = $query->get();

        // Inclure explicitement le champ code dans la réponse (utile si filtré ailleurs)
        $data = $filiaires->map(function ($filiaire) {
            return [
                'id' => $filiaire->id,
                'name' => $filiaire->name,
                'code' => $filiaire->code,
                'cycles' => $filiaire->cycles,
            ];
        });

        return response()->json([
            'data' => $data,
            'message' => "Filières de l'école de l'utilisateur connecté",
        ], Response::HTTP_OK);
    }

    public function delete(Filiaire $filiaire): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $userSchoolId = $user?->school_id;
        if ($userSchoolId && (int) $filiaire->school_id !== (int) $userSchoolId) {
            return response()->json([
                'success' => false,
                'message' => "Accès refusé: cette filière n'appartient pas à votre école.",
            ], 403);
        }

        $filiaire->delete();

        return response()->json([
            'data' => [],
            'message' => 'filiaire delete successfuly',
        ]);
    }

    public function show(Filiaire $filiaire): JsonResponse
    {
        return response()->json([
            'data' => $filiaire->load('cycles.academicLevels'),
            'message' => 'Détail de la filière',
        ], Response::HTTP_OK);
    }
}
