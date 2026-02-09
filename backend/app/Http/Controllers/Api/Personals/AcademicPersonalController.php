<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Personals;

use App\Actions\Personals\ExportPersonalActions;
use App\Actions\Personals\ImportPersonalActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicPersonalRequest;
use App\Http\Requests\UpdateAcademicPersonalRequest;
use App\Models\AcademicPersonal;
use App\Models\User;
use App\Notifications\NewUserCredentialsNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AcademicPersonalController extends Controller
{
    public function __construct(
        public ExportPersonalActions $actions
    ) {}

    /**
     * Statistiques des personnels académiques par mois et par genre.
     *
     * GET /api/v1/hr/academic-personals/stats-by-month
     * Options:
     *   - ?year=2026  : filtrer sur une année précise (sinon année courante)
     *   - ?month=1    : optionnel, ne garder qu’un mois précis
     */
    public function statsByMonth(Request $request): JsonResponse
    {
        $user = Auth::user();
        $schoolId = data_get($user, 'school_id');

        $year = (int) ($request->input('year') ?: now()->year);
        $monthFilter = $request->input('month');

        $query = AcademicPersonal::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, gender, COUNT(*) as total_personnel')
            ->whereYear('created_at', $year)
            ->groupByRaw('YEAR(created_at), MONTH(created_at), gender')
            ->orderByRaw('YEAR(created_at), MONTH(created_at), gender');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($monthFilter) {
            $query->whereMonth('created_at', (int) $monthFilter);
        }

        $rows = $query->get();

        $data = $rows->map(function ($row) {
            return [
                'year' => (int) $row->year,
                'month' => (int) $row->month,
                'gender' => $row->gender,
                'total_personnel' => (int) $row->total_personnel,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Statistiques des personnels par mois de création',
            'data' => $data,
        ]);
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $schoolId = data_get($user, 'school_id');
        $search = request()->input('search');
        $perPage = request()->input('per_page', 15);
        $query = AcademicPersonal::with([
            'father',
            'mother',
            'fonction',
            'academicLevel.cycle.filiaire',
        ])->where('school_id', $schoolId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('post_name', 'like', "%{$search}%")
                    ->orWhere('pre_name', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('identity_card_number', 'like', "%{$search}%");
            });
        }

        $personnel = $query->paginate($perPage);

        $items = $personnel->getCollection()->map(function (AcademicPersonal $personal) {
            $data = $personal->toArray();

            $data['father'] = $personal->father ? [
                'id' => $personal->father->id,
                'name' => $personal->father->name,
            ] : null;

            $data['mother'] = $personal->mother ? [
                'id' => $personal->mother->id,
                'name' => $personal->mother->name,
            ] : null;

            $data['fonction'] = $personal->fonction ? [
                'id' => $personal->fonction->id,
                'name' => $personal->fonction->title,
            ] : null;

            return $data;
        });

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $personnel->currentPage(),
                'per_page' => $personnel->perPage(),
                'total' => $personnel->total(),
                'last_page' => $personnel->lastPage(),
            ],
            'message' => 'Academic personnel recuperer avec succes',
        ]);
    }

    public function store(AcademicPersonalRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $password = Str::random(8);
            $validated = $request->validated();
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('academic_personals', 'public');
                $validated['image'] = basename($path);
            }
            $creatorSchoolId = Auth::user()?->school_id;

            try {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($password),
                    'school_id' => $creatorSchoolId,
                ]);
            } catch (\Illuminate\Database\QueryException $qe) {
                DB::rollBack();
                if ($qe->getCode() === '23000' && str_contains($qe->getMessage(), 'users_email_unique')) {
                    return response()->json([
                        'message' => "L'adresse e-mail est déjà utilisée par un autre utilisateur.",
                        'errors' => [
                            'email' => ["Cette adresse e-mail est déjà utilisée par un autre utilisateur."]
                        ]
                    ], 422);
                }
                throw $qe;
            }

            $personnel = AcademicPersonal::create(array_merge(
                $validated,
                [
                    'user_id' => $user->id,
                    'school_id' => $creatorSchoolId,
                ]
            ));

            $user->notify(new NewUserCredentialsNotification(
                $personnel,
                $password
            ));

            DB::commit();

            return response()->json([
                'data' => $personnel->load([
                    'user',
                    'country',
                    'province',
                    'territory',
                    'commune',
                    'school',
                    'type',
                    'academicLevel.cycle.filiaire',
                    'fonction',
                ]),
                'message' => 'Academic personnel created successfully with user account',
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating academic personnel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $academicPersonal = AcademicPersonal::with([
            'country',
            'province',
            'territory',
            'commune',
            'school',
            'type',
            'academicLevel.cycle.filiaire',
            'fonction',
            'user',
            'document',
        ])->find($id);

        if (! $academicPersonal) {
            return response()->json([
                'message' => 'Academic personnel not found',
            ], 404);
        }

        return response()->json([
            'data' => $academicPersonal,
            'message' => 'Academic personnel retrieved successfully',
        ]);
    }

    public function update(UpdateAcademicPersonalRequest $request, int $id): JsonResponse
    {
        $academicPersonal = AcademicPersonal::find($id);

        if (! $academicPersonal) {
            return response()->json([
                'message' => 'Academic personnel not found',
            ], 404);
        }

        $validated = $request->validated();
        // On ne stocke l'image que si c'est un fichier uploadé
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('academic_personals', 'public');
            $validated['image'] = basename($path);
        } else {
            unset($validated['image']); // On ignore toute valeur image non fichier
        }

        // Met à jour les parents si fournis
        if ($request->filled('father_id')) {
            $academicPersonal->father_id = $request->input('father_id');
        }
        if ($request->filled('mother_id')) {
            $academicPersonal->mother_id = $request->input('mother_id');
        }

        $academicPersonal->update($validated);

        return response()->json([
            'data' => $academicPersonal->load([
                'country', 'province', 'territory', 'commune',
                'school', 'type', 'academicLevel.cycle.filiaire', 'fonction', 'father', 'mother',
            ]),
            'message' => 'Academic personnel updated successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $academicPersonal = AcademicPersonal::find($id);

        if (! $academicPersonal) {
            return response()->json([
                'message' => 'Academic personnel not found',
            ], 404);
        }

        $academicPersonal->delete();

        return response()->json(['message' => 'Academic personnel deleted successfully']);
    }

    public function import(Request $request, ImportPersonalActions $actions): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        if (! $request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $actions->execute($request);

        return response()->json(['message' => 'Import successful']);
    }

    public function export(Request $request): BinaryFileResponse
    {
        return $this->actions->execute($request);
    }
}
