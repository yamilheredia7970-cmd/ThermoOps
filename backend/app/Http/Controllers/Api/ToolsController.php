<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreToolRequest;
use App\Http\Requests\UpdateToolRequest;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ToolsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Tool::class);

        $tools = Tool::query()
            ->with('assignedTechnician:id,name')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25));

        return ToolResource::collection($tools);
    }

    public function store(StoreToolRequest $request): JsonResponse
    {
        $tool = Tool::create([
            ...$request->validated(),
            'status' => $request->validated('status', 'Available'),
        ]);

        return (new ToolResource($tool->load('assignedTechnician:id,name')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Tool $tool): ToolResource
    {
        $this->authorize('view', $tool);

        return new ToolResource($tool->load('assignedTechnician:id,name'));
    }

    public function update(UpdateToolRequest $request, Tool $tool): ToolResource
    {
        $tool->update($request->validated());

        return new ToolResource($tool->load('assignedTechnician:id,name'));
    }

    public function destroy(Tool $tool): Response
    {
        $this->authorize('delete', $tool);

        $tool->delete();

        return response()->noContent();
    }
}
