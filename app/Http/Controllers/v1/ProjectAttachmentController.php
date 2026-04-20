<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Attachment\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Http\Resources\v1\AttachmentResource;
use App\Models\Attachment;
use App\Models\Project;
use App\Services\v1\AttachmentService;

class ProjectAttachmentController extends Controller
{
    public function __construct(private AttachmentService $attachment_service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        if (!$project)
            return ApiResponse::notFound();
        return ApiResponse::success(AttachmentResource::collection($project->attachments));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttachmentRequest $request, Project $project)
    {
        // dd($request->all());
        if (!$project)
            return ApiResponse::notFound('project not found');
        if ($request->hasFile('attachments'))
            $this->attachment_service->upload($project, $request->file('attachments'), 'projects');
        return ApiResponse::success([], 'attachment uploaded successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Attachment $attachment)
    {
        if (!$project)
            return ApiResponse::notFound('project not found');
        if (!$attachment)
            return ApiResponse::notFound("attachment with id : $attachment->id for project id: $project->id  not found");

        $this->attachment_service->delete($project, $attachment->id);
        return ApiResponse::success([], 'attachment deleted successfully');
    }
}
