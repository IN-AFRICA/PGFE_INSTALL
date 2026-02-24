<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\School;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

trait AutoAssignsSchoolContext
{
    /** @var array<string,bool> Cache de présence des colonnes (table:col => bool) */
    private static array $columnCache = [];

    /** @var class-string[] */
    private static array $typeAssignableModels = [
        \App\Models\Registration::class,
        \App\Models\Payment::class,
        \App\Models\Expense::class,
    ];

    public static function bootAutoAssignsSchoolContext(): void
    {
        static::creating(function ($model): void {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            $fillable = method_exists($model, 'getFillable') ? $model->getFillable() : [];
            $guarded = property_exists($model, 'guarded') ? $model->guarded : [];
            $allMassAssignable = $fillable === [] && $guarded === [];

            $canAssign = function (string $attribute) use ($fillable, $allMassAssignable, $model): bool {
                if (! self::hasColumn($model, $attribute)) {
                    return false; // colonne absente en base -> ne rien assigner
                }

                return $allMassAssignable || in_array($attribute, $fillable, true);
            };

            // school_id
            if ($canAssign('school_id') && $user?->school_id && ! isset($model->getAttributes()['school_id'])) {
                $model->school_id = $user->school_id;
            }

            // school_year_id
            if ($canAssign('school_year_id') && ! isset($model->getAttributes()['school_year_id']) && $model->getAttribute('school_id')) {
                $active = SchoolYear::active($model->getAttribute('school_id'));
                if ($active) {
                    $model->school_year_id = $active->id;
                }
            }

            // type_id (ex: Registration / Payment) UNIQUEMENT si colonne existe ET modèle autorisé
            if ($canAssign('type_id') && ! isset($model->getAttributes()['type_id']) && $model->getAttribute('school_id') && self::isTypeAssignable($model)) {
                $school = School::query()->find($model->getAttribute('school_id'));
                if ($school && $school->type_id) {
                    $model->type_id = $school->type_id;
                }
            }
        });
    }

    private static function hasColumn(Model $model, string $column): bool
    {
        $key = $model->getTable().':'.$column;
        if (! array_key_exists($key, self::$columnCache)) {
            try {
                self::$columnCache[$key] = Schema::hasColumn($model->getTable(), $column);
            } catch (Throwable) {
                self::$columnCache[$key] = false; // prudence en cas d'erreur connexion
            }
        }

        return self::$columnCache[$key];
    }

    private static function isTypeAssignable(Model $model): bool
    {
        foreach (self::$typeAssignableModels as $cls) {
            if ($model instanceof $cls) {
                return true;
            }
        }

        return false;
    }
}
