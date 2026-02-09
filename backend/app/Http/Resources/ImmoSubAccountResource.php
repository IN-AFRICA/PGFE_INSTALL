<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ImmoSubAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'immo_sub_account_id' => $this->immo_sub_account_id,
            'school_id' => $this->school_id,
            'user_id' => $this->user_id,
        ];
    }
}
