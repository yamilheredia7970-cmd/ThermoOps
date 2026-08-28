<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TechnicianResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TechniciansController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAnyTechnicians', User::class);

        $technicians = User::query()
            ->role('Technician')
            ->with('technicianProfile')
            ->orderBy('name')
            ->get();

        return TechnicianResource::collection($technicians);
    }

    public function show(User $technician): TechnicianResource
    {
        $this->authorize('viewTechnicianProfile', $technician);

        return new TechnicianResource($technician->load('technicianProfile'));
    }
}
