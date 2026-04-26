<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller; 
use App\Http\Resources\v1\HomeResource; 
use App\Services\v1\HomeService; 

class HomeController extends Controller
{
    public function __construct(private HomeService $homeService)
    {
    }
    public function index(){
        $projects = $this->homeService->OpenProjectsMenu();
        return ApiResponse::paginated(HomeResource::collection($projects));
    } 
}
