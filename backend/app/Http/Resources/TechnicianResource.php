<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->technicianProfile;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ?? $this->initials(),
            'skills' => $profile?->skills ?? [],
            'status' => $profile?->availability_status ?? 'Off Duty',
            // Wired to real assignment/scheduling data once work orders exist (Phase 3).
            'currentJobId' => null,
            'jobsToday' => 0,
            'hoursThisWeek' => 0,
            'rating' => $profile ? (float) $profile->rating : 0.0,
            'completionRate' => $profile?->completion_rate ?? 0,
        ];
    }
}
