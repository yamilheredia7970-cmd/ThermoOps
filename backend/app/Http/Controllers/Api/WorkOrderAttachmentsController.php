<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderAttachmentsController extends Controller
{
    public function store(StoreAttachmentRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('work-orders/'.$workOrder->id, 'public');

        $attachment = $workOrder->attachments()->create([
            'type' => $request->validated('type'),
            'disk' => 'public',
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'uploaded_by' => $request->user()->id,
        ]);

        return (new AttachmentResource($attachment->load('uploadedBy:id,name')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, WorkOrder $workOrder, Attachment $attachment): Response
    {
        $this->authorize('manageAttachments', $workOrder);

        $attachment->delete();

        return response()->noContent();
    }
}
