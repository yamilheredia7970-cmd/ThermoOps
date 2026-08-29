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
            'currentJobId' => $this->current_job_id ?? null,
            'jobsToday' => $this->jobs_today_count ?? 0,
            'hoursThisWeek' => $this->hours_this_week_sum ? (float) $this->hours_this_week_sum : 0.0,
            'rating' => $profile ? (float) $profile->rating : 0.0,
            'completionRate' => $profile?->completion_rate ?? 0,
        ];
    }
}
