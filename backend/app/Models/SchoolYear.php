<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'is_active',
        'description',
    ];

    /** @var array<int|string,self|null> */
    private static array $activeCache = [];

    public static function active(int|string|null $schoolId = null, bool $refresh = false): ?self
    {
        // Normaliser l'identifiant pour cache et requête
        $normalizedId = null;
        if (is_int($schoolId)) {
            $normalizedId = $schoolId;
        } elseif (is_string($schoolId)) {
            $trim = mb_trim($schoolId);
            if ($trim !== '' && ctype_digit($trim)) {
                $normalizedId = (int) $trim;
            }
        }

        // Si ID non défini ou == 0, utiliser le cache global (comportement historique)
        $useGlobal = ! $normalizedId; // null ou 0 => global
        $key = $useGlobal ? 'global' : $normalizedId;

        if ($refresh || ! array_key_exists($key, self::$activeCache)) {
            $query = self::query()->where('is_active', true)->orderByDesc('id');
            if (! $useGlobal) {
                $query->where('school_id', $normalizedId);
            }
            self::$activeCache[$key] = $query->first();
        }

        return self::$activeCache[$key];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Scope pour année active d’une école
    // Exemple d'utilisation du scope dans un controller
    // $activeYear = SchoolYear::activeForSchool($school->id);
    public function scopeActiveForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId)
            ->where('is_active', true)->first();
    }

    // Empêcher plusieurs années actives pour une même école

    protected static function booted()
    {
        self::saving(function ($model) {
            if ($model->is_active) {
                static::where('school_id', $model->school_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_active' => false]);
                // Rafraîchir cache
                self::$activeCache[$model->school_id ?? 'global'] = $model;
            }
        });
    }
}
