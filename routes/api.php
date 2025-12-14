<?php

use App\Http\Controllers\Api\JobController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/jobs/metadata', [JobController::class, 'metadata']);
    Route::get('/jobs/stats/summary', [JobController::class, 'stats']);
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{job}', [JobController::class, 'show'])->whereUlid('job');
});
