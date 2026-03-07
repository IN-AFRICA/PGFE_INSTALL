<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;


final class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request)
    {
        $validatedData = $request->validated();

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
            'success' => true,
            'message' => 'utilisateur cree avec succees ',
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_CREATED);
    }
}
