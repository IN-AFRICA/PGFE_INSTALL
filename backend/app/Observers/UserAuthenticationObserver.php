<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

final class UserAuthenticationObserver
{
    public function updated(User $user): void
    {
        if (auth()->check() && auth()->user()->id === $user->id) {
            $user->last_login_at = Carbon::now();
            $user->saveQuietly();
        }
    }

    public function authenticated(User $user): void
    {
        $user->last_login_at = Date::now();
        $user->saveQuietly();
    }
}
