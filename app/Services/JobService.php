<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JobService
{
    public function getMetadata(): array
    {
        return [
            'statuses' => $this->getDistinctStatuses(),
            'job_types' => $this->getDistinctJobTypes(),
        ];
    }

    public function getStats(): array
    {
        return Cache::remember('jobs_stats', 300, function () {
            $stats = DB::selectOne("
                SELECT
                    COUNT(*) as total_jobs,
                    COALESCE(SUM(j.invoice_amount), 0) as total_revenue,
                    COALESCE(SUM(
                        j.invoice_amount - (j.labor_hours * j.labor_rate) - COALESCE(m.material_cost, 0)
                    ), 0) as total_profit,
                    COALESCE(AVG(
                        CASE WHEN j.invoice_amount = 0 THEN 0
                        ELSE ((j.invoice_amount - (j.labor_hours * j.labor_rate) - COALESCE(m.material_cost, 0)) / j.invoice_amount) * 100
                        END
                    ), 0) as average_margin
                FROM jobs j
                LEFT JOIN (
                    SELECT job_id, SUM(cost) as material_cost
                    FROM materials
                    GROUP BY job_id
                ) m ON m.job_id = j.id
            ");

            return [
                'total_jobs' => (int) $stats->total_jobs,
                'total_revenue' => round($stats->total_revenue, 2),
                'total_profit' => round($stats->total_profit, 2),
                'average_margin' => round($stats->average_margin, 2),
            ];
        });
    }

    private function getDistinctStatuses(): array
    {
        return Job::select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->values()
            ->toArray();
    }

    private function getDistinctJobTypes(): array
    {
        return Job::select('job_type')
            ->distinct()
            ->orderBy('job_type')
            ->pluck('job_type')
            ->values()
            ->toArray();
    }
}
