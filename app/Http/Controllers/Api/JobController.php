<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index(): JsonResponse
    {
        $perPage = min(request('per_page', 20), 100);

        $jobs = Job::with('materials')
            ->search(request('search'))
            ->filter(request()->only(['status', 'job_type']))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => JobResource::collection($jobs->items()),
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'last_page' => $jobs->lastPage(),
                'from' => $jobs->firstItem(),
                'to' => $jobs->lastItem(),
            ],
            'links' => [
                'first' => $jobs->url(1),
                'last' => $jobs->url($jobs->lastPage()),
                'prev' => $jobs->previousPageUrl(),
                'next' => $jobs->nextPageUrl(),
            ]
        ]);
    }

    public function show(Job $job): JsonResponse
    {
        $job->load('materials');

        return response()->json([
            'success' => true,
            'data' => new JobResource($job)
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = DB::table('jobs')
            ->selectRaw('
                COUNT(*) as total_jobs,
                COALESCE(SUM(invoice_amount), 0) as total_revenue,

                COALESCE(SUM(
                    invoice_amount
                    - (labor_hours * labor_rate)
                    - (SELECT COALESCE(SUM(cost), 0) FROM materials WHERE materials.job_id = jobs.id)
                ), 0) as total_profit,

                COALESCE(AVG(
                    CASE
                        WHEN invoice_amount = 0 THEN 0
                        ELSE
                            ((invoice_amount - (labor_hours * labor_rate) - (
                                SELECT COALESCE(SUM(cost), 0) FROM materials WHERE materials.job_id = jobs.id
                            )) / invoice_amount) * 100
                    END
                ), 0) as average_margin
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_jobs' => (int) $stats->total_jobs,
                'total_revenue' => round($stats->total_revenue, 2),
                'total_profit' => round($stats->total_profit, 2),
                'average_margin' => round($stats->average_margin, 2),
            ]
        ]);
    }
}
