<?php

namespace App\Services;

use App\Models\Job;
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

        return [
            'total_jobs' => (int) $stats->total_jobs,
            'total_revenue' => round($stats->total_revenue, 2),
            'total_profit' => round($stats->total_profit, 2),
            'average_margin' => round($stats->average_margin, 2),
        ];
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
