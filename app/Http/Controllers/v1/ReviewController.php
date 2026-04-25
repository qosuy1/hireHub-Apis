<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Requests\v1\Review\StoreReviewRequest;
use App\Models\Project;
use App\Models\Review;
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
     * Store a newly created review for the project.
     * Route: POST /projects/{project}/review/project
     */
    public function storeProjectReview(StoreReviewRequest $request, Project $project)
    {
        if (!$project) {
            return ApiResponse::notFound('project not found');
        }
        if ($project->status !== 'closed')
            return ApiResponse::error('project not closed yet..');

        $created = $this->reviewService->reviewProject(
            $project,
            $request->user(),
            $request->validated()
        );

        if (!$created) {
            return ApiResponse::forbidden('You are not allowed to review this, or have already reviewed it.');
        }

        return ApiResponse::success([], 'Project review submitted successfully.');
    }

    /**
     * Store a newly created review for the freelancer on the project.
     * Route: POST /projects/{project}/review/freelancer
     */
    public function storeFreelancerReview(StoreReviewRequest $request, Project $project)
    {
        if (!$project) {
            return ApiResponse::notFound('project not found');
        }
        if ($project->status !== 'closed')
            return ApiResponse::error('project not closed yet..');

        $created = $this->reviewService->reviewFreelancer(
            $project,
            $request->user(),
            $request->validated()
        );

        if (!$created) {
            return ApiResponse::forbidden('You are not allowed to review this, or have already reviewed it.');
        }

        return ApiResponse::success([], 'Freelancer review submitted successfully.');
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $updated = $this->reviewService->updateReview($review, $request->user(), $request->validated());
        if (!$updated) {
            return ApiResponse::forbidden('You are not allowed to update this review.');
        }
        return ApiResponse::success([], 'Review updated successfully.');
    }

    public function destroy(Review $review)
    {
        $deleted = $this->reviewService->deleteReview($review, auth()->user());
        if (!$deleted) {
            return ApiResponse::forbidden('You are not allowed to delete this review.');
        }
        return ApiResponse::success([], 'Review deleted successfully.');
    }
}

