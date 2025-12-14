<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'WIZniche Job Costing Analyzer API',
        'version' => '1.0.0',
        'status' => 'active',
        'endpoints' => [
            'jobs_list' => '/api/v1/jobs',
            'job_detail' => '/api/v1/jobs/{id}',
            'stats' => '/api/v1/jobs/stats/summary',
            'stats' => '/api/v1/jobs/metadata',
        ],
        'documentation' => 'https://github.com/Fajendagba/wizniche-demo'
    ]);
});
