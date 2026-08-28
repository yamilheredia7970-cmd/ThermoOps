<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UsersController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25));

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            ...collect($data)->only(['name', 'email', 'phone', 'password'])->all(),
            'status' => $data['status'] ?? 'active',
        ]);

        $user->assignRole($data['role']);

        if ($data['role'] === 'Technician') {
            $user->technicianProfile()->create([
                'skills' => $data['skills'] ?? [],
            ]);
        }

        return (new UserResource($user->load('roles', 'technicianProfile')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user->load('roles', 'technicianProfile'));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $data = $request->validated();

        $user->fill(collect($data)->only(['name', 'email', 'phone', 'password', 'status'])->all());
        $user->save();

        if (array_key_exists('role', $data)) {
            $user->syncRoles([$data['role']]);
        }

        if ($user->hasRole('Technician') && array_key_exists('skills', $data)) {
            $user->technicianProfile()->updateOrCreate([], ['skills' => $data['skills']]);
        }

        return new UserResource($user->load('roles', 'technicianProfile'));
    }

    public function destroy(User $user): Response
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->noContent();
    }
}
