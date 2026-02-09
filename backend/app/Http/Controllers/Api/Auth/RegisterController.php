<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

// Assure-toi que le modèle correspond à ta structure

final class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        // Si l'inscription est faite par un utilisateur authentifié,
        // on hérite de sa school_id pour rattacher le nouvel user à la même école.
        /** @var User|null $creator */
        $creator = auth()->user();
        $creatorSchoolId = $creator?->school_id ?? null;

        $payload = [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ];

        if ($creatorSchoolId) {
            $payload['school_id'] = $creatorSchoolId;
        }

        $user = User::query()->create($payload);

        // Attribution auto du rôle tiers si disponible
        if (method_exists($user, 'assignRole')) {
            try {
                if (! $user->hasRole('tiers')) {
                    $user->assignRole('tiers');
                }
            } catch (Throwable $e) {
                // silencieux: si role absent/seeder pas encore lancé
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}
