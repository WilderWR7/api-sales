<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'total' => (float) $this->total,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'details' => SaleDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
