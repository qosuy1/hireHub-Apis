<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\project\StoreProjectRequest;
use App\Http\Requests\V1\project\UpdateProjectRequest;
use App\Http\Resources\v1\ProjectResource;
use App\Models\Offer;
use App\Models\Project;
use App\Services\V1\ProjectService;
use Illuminate\Support\Facades\Config;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // the solve for phase 3
        // حل مشكلة البطء داخل ال Service
        $data = $this->projectService->getAllProjects(request()->query());
        return ApiResponse::paginated(ProjectResource::collection($data));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $project = $this->projectService->store($data);
        return ApiResponse::success(new ProjectResource($project), 'project created successfully !', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        if (!$project)
            return ApiResponse::notFound('project not found');

        $data = $project->load(['tags', 'attachments', 'offers']);
        return ApiResponse::success(new ProjectResource($data));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        if (!$project)
            return ApiResponse::notFound('project not found');

        $project = $this->projectService->update($project, $request->validated());
        return ApiResponse::success([
            new ProjectResource($project),
            'meta' => [
                'updated_at' => $project->updated_at->toDateTimeString(),
            ],
        ], 'project updated successfully !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if (!$project)
            return ApiResponse::notFound('project not found');

        $project = $this->projectService->delete($project);
        return ApiResponse::success([], 'project deleted successfully with all attachments !');
    }

    public function acceptOffer(Project $project, Offer $offer)
    {
        
        if (!$project)
            return ApiResponse::notFound('project not found');
        if (!$offer)
            return ApiResponse::notFound('offer not found');
        if ($offer->project_id !== $project->id)
            return ApiResponse::notFound('this offer not for this project');
        if ($project->status != 'open')
            return ApiResponse::error("you can't accept offer because project is not open");

        if ($this->projectService->acceptOffer($project, $offer))
            return ApiResponse::success(new ProjectResource($project->load('acceptedOffer')), "offer accepted successfully");

        return ApiResponse::error("you are not the project owner");
    }
}
