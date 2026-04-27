<?php
namespace App\Services\v1;

use App\Models\Offer;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomeService
{
    public function OpenProjectsMenu()
    {
        $page = request()->get('page', 1);
        $key = "open_projects_api_page_{$page}";
        return Cache::tags(['projects'])
            ->remember($key, now()->addDay(), function () {
                return Project::open()
                    ->withCount('offers')
                    ->with(['tags', 'user'])
                    ->latest()
                    ->paginate(10);
            });
    }
}