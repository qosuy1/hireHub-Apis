<?php
namespace App\Services\v1;

use App\Models\Offer;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

class HomeService
{
    public function OpenProjectsMenu()
    {
        return Project::open()->withCount('offers')->with(['tags', 'user'])->latest()->budgetAbove($filters['min_budget'] ?? null)
            ->when($filters['this_month'] ?? null, fn($q) => $q->thisMonth())
            ->paginate(15)->getCollection();
    }
}