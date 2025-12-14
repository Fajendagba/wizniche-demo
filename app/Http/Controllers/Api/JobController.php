<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\Job;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    public function __construct(
        private JobService $jobService
    ) {
    }
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
        return response()->json([
            'success' => true,
            'data' => $this->jobService->getStats()
        ]);
    }

    public function metadata(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->jobService->getMetadata()
        ]);
    }
}
