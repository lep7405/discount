<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'started_at' => $this->started_at,
            'expired_at' => $this->expired_at,
            'type' => $this->type,
            'value' => $this->value,
            'usage_limit' => $this->usage_limit,
            'trial_days' => $this->trial_days,
            'discount_month' => $this->discount_month,
        ];
    }
}
