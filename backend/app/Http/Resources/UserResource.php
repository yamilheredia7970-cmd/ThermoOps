<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar ?? $this->initials(),
            'status' => $this->status,
            'roles' => $this->getRoleNames(),
            'lastLoginAt' => $this->last_login_at,
            'createdAt' => $this->created_at,
        ];
    }
}
