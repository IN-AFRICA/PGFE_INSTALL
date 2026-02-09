<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PresenceRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $meta = [
            'classroom_id' => $this->classroom_id,
            'student_id' => $this->student_id,
            'school_id' => $this->school_id,
        ];

        // Statut textuel unique selon la règle demandée
        // Priorité: absent_justified -> sick -> presence(true)=present -> else=absent
        if ($this->absent_justified) {
            $status = 'absent_justified';
        } elseif ($this->sick) {
            $status = 'sick';
        } elseif ($this->presence === true) {
            $status = 'present';
        } else {
            $status = 'absent';
        }

        return [
            'id' => $this->id,
            'student' => $this->student ? $this->student->name.' '.$this->student->firstname : null,
            'school' => $this->school?->name,
            'classroom' => $this->classroom?->name,
            'presence' => (bool) $this->presence,
            'absent_justified' => (bool) $this->absent_justified,
            'sick' => (bool) $this->sick,
            'status' => $status,
            'meta' => $meta,
            'date' => $this->created_at ? $this->created_at->toDateString() : null,
        ];
    }
}
