<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ANewComptabilityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'account_plan_id' => $this->account_plan_id,
            'sub_account_plan_id' => $this->sub_account_plan_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'justification' => $this->justification,
            'school_id' => $this->school_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
