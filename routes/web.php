<?php

use App\Services\V1\ProjectService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return (new ProjectService())->getAllProjects()->toArray();
});
