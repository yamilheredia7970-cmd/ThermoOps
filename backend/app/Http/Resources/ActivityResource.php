<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'timestamp' => $this->occurred_at->toIso8601String(),
            'relatedId' => $this->subject_id !== null ? (string) $this->subject_id : null,
            'actor' => $this->whenLoaded('actor', fn () => $this->actor?->name ?? 'System'),
        ];
    }
}
