<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToSchoolUser
{
    public static function bootBelongsToSchoolUser()
    {
        static::creating(function (Model $model) {
            if (Auth::check()) {
                $model->school_id = Auth::user()->school_id;
                $model->user_id = Auth::id();
            }
        });
    }
}
