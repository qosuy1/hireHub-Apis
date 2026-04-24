<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\DashboardController;
use App\Http\Controllers\v1\FreelancerSkillController;
use App\Http\Controllers\v1\HomeController;
use App\Http\Controllers\v1\OfferAttachmentController;
use App\Http\Controllers\v1\OfferController;
use App\Http\Controllers\v1\ProjectAttachmentController;
use App\Http\Controllers\v1\ProjectController;
use App\Http\Controllers\v1\ReviewController;
use App\Http\Controllers\v1\SkillController;
use App\Http\Controllers\V1\FreelancerProfileController;
use App\Http\Resources\v1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return new UserResource($request->user()->load('freelancerProfile'));
})->middleware('auth:sanctum')->name('api.user');

// registeration and logging routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');



Route::prefix('v1')->group(function () {
    // HomePage
    Route::get("/", [HomeController::class, 'index']);
    // Dashboard
    Route::get("/dashboard", [DashboardController::class, 'index'])
        ->middleware(['auth:sanctum', 'admin']);

        
    // Offers
    // offer attachment managment
    Route::get('offers/{offer}/attachments', [OfferAttachmentController::class, 'index']);
    Route::post('offers/{offer}/attachments', [OfferAttachmentController::class, 'store'])
        ->middleware(['auth:sanctum']);
    Route::delete('offers/{offer}/attachments/{attachment}', [OfferAttachmentController::class, 'destroy'])
        ->middleware(['auth:sanctum']);


    // Projects
    Route::apiResource('projects', ProjectController::class)
        ->middlewareFor(['store', 'update', 'destroy'], 'auth:sanctum');

    // project offers access
    Route::post('projects/{project}/accept-offer/{offer}', [ProjectController::class, 'acceptOffer'])
        ->name('projects.accept-offer')
        ->middleware(['auth:sanctum']);
    Route::apiResource("projects/{project}/offers", OfferController::class)
        ->middlewareFor(['store', 'update', 'destroy'], ['auth:sanctum', 'verified_freelancer']);

    // project attachment managment
    Route::get('projects/{project}/attachments', [ProjectAttachmentController::class, 'index']);
    Route::post('projects/{project}/attachments', [ProjectAttachmentController::class, 'store'])
        ->middleware(['auth:sanctum']);
    Route::delete('projects/{project}/attachments/{attachment}', [ProjectAttachmentController::class, 'destroy'])
        ->middleware(['auth:sanctum']);
    // project Reviews
    Route::post('projects/{project}/review', [ReviewController::class, 'store'])
        ->middleware(['auth:sanctum']);


    // skills
    Route::apiResource('/skills', SkillController::class)
        ->middlewareFor(['store', 'update', 'destroy'], ['auth:sanctum', 'admin']);


    // Profiles
    Route::apiResource('/freelancer-profiles', FreelancerProfileController::class)
        ->middlewareFor(['store', 'update', 'destroy'], 'auth:sanctum')
        ->middlewareFor(['update', 'destroy'], 'verified_freelancer');
    // Profile skills
    Route::group([
        'prefix' => '/freelancer-profiles',
        'middleware' => ['auth:sanctum', 'verified_freelancer']
    ], function () {
        Route::post('/{freelancerProfile}/skills', [FreelancerSkillController::class, 'store']);
        Route::put('/{freelancerProfile}/skills/{skill}', [FreelancerSkillController::class, 'update']);
        Route::delete('/{freelancerProfile}/skills/{skill}', [FreelancerSkillController::class, 'destroy']);
    });


});
