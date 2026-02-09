<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

final class AdminUserController extends Controller
{
    public function index()
    {
        // Vérification simple permission via rôle (super-admin ou school-admin)
        /** @var User|null $current */
        $current = Auth::user();
        if (! $current || (! $current->hasAnyRole(['admin', 'admin-ecole']) && ! $current->can('users.view'))) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json(User::with('roles')->paginate());
    }

    public function store(Request $request)
    {
        // Règles:
        // admin: peut créer admin-ecole & tiers
        // admin-ecole: peut créer uniquement tiers
        /** @var User|null $current */
        $current = Auth::user();
        if (! $current || ! $current->hasRole('admin')) {
            // si admin-ecole -> refus si tentative autre que tiers
            if ($current?->hasRole('admin-ecole')) {
                if (in_array($request->input('role'), ['admin', 'admin-ecole'])) {
                    return response()->json(['message' => 'Non autorisé à créer ce type d\'utilisateur'], 403);
                }
            } else {
                return response()->json(['message' => 'Non autorisé'], 403);
            }
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', 'string', Rule::in(['admin-ecole', 'tiers', 'admin'])],
            // allow admin to pass school_id; will be ignored/overridden for admin-ecole creators
            'school_id' => ['sometimes', 'integer', 'exists:schools,id'],
        ]);

        // Défaut rôle = tiers si non fourni
        $role = $data['role'] ?? 'tiers';

        // Déterminer la school_id qui sera assignée au nouvel utilisateur
        $newUserSchoolId = null;
        if ($current->hasRole('admin-ecole')) {
            // force la même école que le créateur
            $newUserSchoolId = $current->school_id;
        } elseif ($current->hasRole('admin')) {
            // admin peut préciser school_id via la requête
            $newUserSchoolId = $data['school_id'] ?? null;
        } else {
            // fallback: si authentifié mais pas de rôle particulier
            $newUserSchoolId = $current->school_id ?? null;
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        if ($newUserSchoolId !== null) {
            $payload['school_id'] = $newUserSchoolId;
        }

        $user = User::create($payload);

        // Role assignment rules
        // Si créateur est admin, il ne peut pas créer un autre admin (option: autoriser ? => ici on autorise seulement admin-ecole & tiers)
        if ($current->hasRole('admin') && $role === 'admin') {
            return response()->json(['message' => 'Création d\'un second admin via API restreinte'], 403);
        }

        $user->assignRole($role);

        return response()->json($user->load('roles'), 201);
    }

    public function update(Request $request, User $user)
    {
        /** @var User|null $current */
        $current = Auth::user();
        if (! $current || (! $current->hasAnyRole(['admin', 'admin-ecole']))) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:6'],
            'role' => ['sometimes', 'string'],
            'school_id' => ['sometimes', 'integer', 'exists:schools,id'],
        ]);

        if (isset($data['role'])) {
            // Restrictions
            // admin-ecole ne peut modifier que vers tiers
            if ($current->hasRole('admin-ecole')) {
                if ($data['role'] !== 'tiers') {
                    return response()->json(['message' => 'Non autorisé (modification rôle)'], 403);
                }
                // ne peut pas toucher un admin
                if ($user->hasRole('admin')) {
                    return response()->json(['message' => 'Non autorisé (cible admin)'], 403);
                }
            }
            // admin peut changer vers admin-ecole ou tiers mais pas créer autre admin ici
            if ($current->hasRole('admin') && $data['role'] === 'admin' && ! $user->hasRole('admin')) {
                return response()->json(['message' => 'Promotion admin interdite ici'], 403);
            }
            $user->syncRoles([$data['role']]);
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Gestion de la permission de mise �� jour de school_id
        if (array_key_exists('school_id', $data)) {
            // Seuls les admins peuvent définir/modifier librement school_id
            if (! $current->hasRole('admin')) {
                // admin-ecole ne peut pas changer l'école d'un autre user
                return response()->json(['message' => 'Non autorisé à modifier l\'école'], 403);
            }
            // autorisé : on applique
            $user->school_id = $data['school_id'];
            // retirer de $data pour éviter double update
            unset($data['school_id']);
        } else {
            // Si role changé vers admin-ecole et aucun school_id fourni,
            // si le modificateur est admin-ecole on force la school du modificateur
            if (isset($data['role']) && $data['role'] === 'admin-ecole' && $current->hasRole('admin-ecole')) {
                $user->school_id = $current->school_id;
            }
        }

        // Appliquer les autres champs
        $user->update($data);

        // Sauvegarder la school_id si modifiée via l'affectation directe
        if (isset($user->school_id)) {
            $user->save();
        }

        return response()->json($user->load('roles'));
    }

    /**
     * Assign or change the school for a user. Only accessible by global admins.
     * PATCH /api/v1/admin/users/{user}/school
     */
    public function assignSchool(Request $request, User $user)
    {
        /** @var User|null $current */
        $current = Auth::user();
        if (! $current || ! $current->hasRole('admin')) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $data = $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,id'],
        ]);

        $old = $user->school_id;
        $user->school_id = $data['school_id'];
        $user->save();

        return response()->json([
            'message' => 'School updated successfully.',
            'user' => $user->load('roles'),
            'old_school_id' => $old,
        ]);
    }

    public function destroy(User $user)
    {
        /** @var User|null $current */
        $current = Auth::user();
        if ($user->hasRole('admin') && ! $current?->hasRole('admin')) {
            return response()->json(['message' => 'Interdit de supprimer un admin'], Response::HTTP_FORBIDDEN);
        }
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}
