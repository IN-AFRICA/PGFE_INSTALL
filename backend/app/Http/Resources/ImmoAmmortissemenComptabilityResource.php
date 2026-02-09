<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ImmoAmmortissemenComptabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'model' => $this->model,
            'amount' => $this->amount,
            'purchase_date' => $this->purchase_date,
            'number_years' => $this->number_years,
            'immo_account_id' => $this->immo_account_id,
            'immo_sub_account_id' => $this->immo_sub_account_id,
            'school_id' => $this->school_id,
            'user_id' => $this->user_id,
        ];
    }
}
