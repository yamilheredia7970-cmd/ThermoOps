<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
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
            'type' => $this->type,
            'url' => $this->url(),
            'originalFilename' => $this->original_filename,
            'uploadedBy' => $this->whenLoaded('uploadedBy', fn () => $this->uploadedBy?->name),
            'createdAt' => $this->created_at,
        ];
    }
}
