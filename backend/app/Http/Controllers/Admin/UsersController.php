<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['roles:id,name', 'school:id,name']);

        if ($search = mb_trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($role = $request->get('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }
        if ($schoolId = $request->get('school_id')) {
            $query->where('school_id', (int) $schoolId);
        }

        $users = $query->latest('id')
            ->paginate(20, ['id', 'name', 'email', 'school_id'])
            ->appends($request->query());

        $roles = Role::query()->orderBy('name')->pluck('name');
        $schools = School::query()->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.users.index', compact('users', 'roles', 'schools'));
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);
        $schools = School::query()->orderBy('name')->get(['id', 'name']);
        $breadcrumbs = [
            ['label' => 'Utilisateurs', 'url' => route('admin.users.index')],
            ['label' => 'Création', 'url' => '#']
        ];

        return view('backend.pages.users.create', compact('roles', 'schools', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed par cast
            'school_id' => $data['school_id'] ?? null,
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);
        $schools = School::query()->orderBy('name')->get(['id', 'name']);
        $userRoles = $user->roles->pluck('name')->all();
        $breadcrumbs = [
            ['label' => 'Utilisateurs', 'url' => route('admin.users.index')],
            ['label' => 'Modifier ' . $user->name, 'url' => '#']
        ];

        return view('backend.pages.users.edit', compact('user', 'roles', 'schools', 'userRoles', 'breadcrumbs'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'school_id' => $data['school_id'] ?? null,
        ];
        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }
        $user->update($payload);
        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        // On supprime aussi le profil académique associé s'il existe
        if ($user->academicPersonal) {
            $user->academicPersonal->delete();
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }
}
