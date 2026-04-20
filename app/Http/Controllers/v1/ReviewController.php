<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Review\StoreReviewRequest;
use App\Models\Project;
use App\Observers\ReviewObserver;
use App\Services\v1\ReviewService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(ReviewObserver::class)]
class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviewService)
    {
    }

    /**
     * Store a newly created review in storage.
     * Route: POST /projects/{project}/review
     */
    public function store(StoreReviewRequest $request, Project $project)
    {
        if (!$project) {
            return ApiResponse::notFound('project not found');
        }
        if ($project->status !== 'closed')
            return ApiResponse::error('project not closed yet..');

        $created = $this->reviewService->createReview(
            $project,
            $request->user(),
            $request->validated()
        );

        if (!$created) {
            return ApiResponse::forbidden('You are not allowed to review this, or have already reviewed it.');
        }

        return ApiResponse::success([], 'Review submitted successfully.');
    }
}

