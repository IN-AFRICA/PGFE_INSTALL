<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AmmortissementComptabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'justification' => $this->justification,
            'date_ammortissement' => $this->date_ammortissement,
            'amount' => $this->amount,
            'immo_account_id' => $this->immo_account_id,
            'immo_sub_account_id' => $this->immo_sub_account_id,
            'school_id' => $this->school_id,
            'user_id' => $this->user_id,
            'annalytique_comptability_id' => $this->annalytique_comptability_id,
        ];
    }
}
