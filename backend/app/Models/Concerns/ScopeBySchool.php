<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

use function data_get;

/**
 * Global scope conditionnel multi-école.
 * - Filtre automatiquement par school_id de l'utilisateur connecté (si défini).
 * - Ne filtre pas si rôle admin (global) ou utilisateur sans school_id.
 */
trait ScopeBySchool
{
    public static function bootScopeBySchool(): void
    {
        static::addGlobalScope('by_school', function (Builder $builder): void {
            $user = Auth::user();
            if (! $user) {
                return; // invité => pas de filtrage (géré par middleware d'auth en amont)
            }
            // Seul SUPER-ADMIN voit tout; les admins sont filtrés par leur école
            if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                return; // super-admin global voit tout
            }

            $schoolId = data_get($user, 'school_id');
            if (! $schoolId) {
                // Utilisateur connecté sans école associée => aucun résultat
                $builder->whereRaw('1 = 0');

                return;
            }

            $model = $builder->getModel();
            $table = $model->getTable();

            // 1) Filtrer directement par colonne school_id si disponible
            try {
                if (Schema::hasColumn($table, 'school_id')) {
                    $builder->where($table.'.school_id', $schoolId);
                    return;
                }
            } catch (Throwable $e) {}

            // 2) Filiaire : relation directe school_id
            if ($model instanceof \App\Models\Filiaire) {
                $builder->where('school_id', $schoolId);
                return;
            }

            // 3) Cycle : via filiaire.school_id
            if ($model instanceof \App\Models\Cycle) {
                $builder->whereHas('filiaire', function (Builder $q) use ($schoolId): void {
                    $q->where('school_id', $schoolId);
                });
                return;
            }

            // 4) AcademicLevel : via cycle.filiaire.school_id
            if ($model instanceof \App\Models\AcademicLevel) {
                $builder->whereHas('cycle.filiaire', function (Builder $q) use ($schoolId): void {
                    $q->where('school_id', $schoolId);
                });
                return;
            }

            // 5) Classroom : via academicLevel.cycle.filiaire.school_id
            if ($model instanceof \App\Models\Classroom) {
                $builder->whereHas('academicLevel.cycle.filiaire', function (Builder $q) use ($schoolId): void {
                    $q->where('school_id', $schoolId);
                });
                return;
            }

            // 6) Journal comptable : via InputAccount / OutputAccount / AccountPlan
            if ($model instanceof \App\Models\Journal) {
                $builder->where(function (Builder $q) use ($schoolId): void {
                    $q->whereHas('inputAccount', function (Builder $q2) use ($schoolId): void {
                        $q2->where('school_id', $schoolId);
                    })->orWhereHas('outputAccount', function (Builder $q2) use ($schoolId): void {
                        $q2->where('school_id', $schoolId);
                    })->orWhereHas('accountPlan', function (Builder $q2) use ($schoolId): void {
                        $q2->where('school_id', $schoolId);
                    });
                });

                return;
            }

            // Sinon: pas de filtrage appliqué (modèle non relié à une école)
        });
    }

    /**
     * Permet de supprimer le scope manuellement si besoin.
     */
    public static function withoutSchoolScope(): callable
    {
        return function (Builder $builder): Builder {
            return $builder->withoutGlobalScope('by_school');
        };
    }
}
