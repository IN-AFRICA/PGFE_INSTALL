<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPersonal;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RoleWebController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::withCount('users')->get();
        $permissions = Permission::all();
        
        // If a role is selected, show its users (filtered by academic personal if needed)
        $selectedRole = null;
        $users = collect();
        if ($roleId = $request->get('role_id')) {
            $selectedRole = Role::find($roleId);
            if ($selectedRole) {
                $users = User::role($selectedRole->name)
                    ->with(['academicPersonal.school', 'academicPersonal.fonction', 'school'])
                    ->paginate(15);
            }
        }

        // To assign new academic personal, we need a list of those without the role
        $academicPersonnels = AcademicPersonal::with(['user', 'school'])->get();

        return view('backend.pages.roles.index', compact('roles', 'permissions', 'selectedRole', 'users', 'academicPersonnels'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        Role::create(['name' => $request->name]);
        return back()->with('success', 'Rôle créé avec succès.');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate(['name' => 'required|unique:roles,name,' . $role->id]);
        $role->update(['name' => $request->name]);
        return back()->with('success', 'Rôle mis à jour.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Impossible de supprimer le rôle super-admin.');
        }
        $role->delete();
        return back()->with('success', 'Rôle supprimé.');
    }

    /**
     * Assign a role to an Academic Personal's user
     */
    public function assign(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $role = Role::findById($request->role_id);
        $user = User::find($request->user_id);
        
        $user->assignRole($role);

        return back()->with('success', "Rôle {$role->name} assigné à {$user->name}.");
    }

    /**
     * Remove a role from a user
     */
    public function revoke(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $role = Role::findById($request->role_id);
        $user = User::find($request->user_id);
        
        if ($role->name === 'super-admin' && User::role('super-admin')->count() <= 1) {
            return back()->with('error', "Impossible de retirer le dernier super-admin.");
        }

        $user->removeRole($role);

        return back()->with('success', "Rôle {$role->name} retiré de {$user->name}.");
    }
}
