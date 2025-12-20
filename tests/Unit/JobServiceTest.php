<?php

namespace Tests\Unit;

use App\Models\Job;
use App\Models\Material;
use App\Services\JobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobServiceTest extends TestCase
{
    use RefreshDatabase;

    private JobService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new JobService();
    }

    public function test_gets_stats_for_multiple_jobs(): void
    {
        // Create jobs with known values for predictable calculations
        $job1 = Job::factory()->create([
            'invoice_amount' => 1000.00,
            'labor_hours' => 10,
            'labor_rate' => 50.00, // labor_cost = 500
        ]);
        Material::factory()->create(['job_id' => $job1->id, 'cost' => 200.00]);
        // Job 1: profit = 1000 - 500 - 200 = 300, margin = 30%

        $job2 = Job::factory()->create([
            'invoice_amount' => 2000.00,
            'labor_hours' => 20,
            'labor_rate' => 40.00, // labor_cost = 800
        ]);
        Material::factory()->create(['job_id' => $job2->id, 'cost' => 400.00]);
        // Job 2: profit = 2000 - 800 - 400 = 800, margin = 40%

        $stats = $this->service->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_jobs', $stats);
        $this->assertArrayHasKey('total_revenue', $stats);
        $this->assertArrayHasKey('total_profit', $stats);
        $this->assertArrayHasKey('average_margin', $stats);

        $this->assertEquals(2, $stats['total_jobs']);
        $this->assertEquals(3000.00, $stats['total_revenue']); // 1000 + 2000
        $this->assertEquals(1100.00, $stats['total_profit']); // 300 + 800
        $this->assertEquals(35.00, $stats['average_margin']); // (30 + 40) / 2
    }

    public function test_gets_stats_with_no_jobs(): void
    {
        $stats = $this->service->getStats();

        $this->assertEquals(0, $stats['total_jobs']);
        $this->assertEquals(0.00, $stats['total_revenue']);
        $this->assertEquals(0.00, $stats['total_profit']);
        $this->assertEquals(0.00, $stats['average_margin']);
    }

    public function test_stats_exclude_zero_invoice_jobs_from_margin(): void
    {
        // Create a job with zero invoice amount
        Job::factory()->create([
            'invoice_amount' => 0.00,
            'labor_hours' => 10,
            'labor_rate' => 50.00,
        ]);

        // Create a normal job
        $job2 = Job::factory()->create([
            'invoice_amount' => 1000.00,
            'labor_hours' => 10,
            'labor_rate' => 50.00, // labor_cost = 500
        ]);
        Material::factory()->create(['job_id' => $job2->id, 'cost' => 100.00]);
        // Job 2: profit = 1000 - 500 - 100 = 400, margin = 40%

        $stats = $this->service->getStats();

        $this->assertEquals(2, $stats['total_jobs']);
        // Average margin should be (0 + 40) / 2 = 20% because zero invoice jobs are set to 0% margin
        $this->assertEquals(20.00, $stats['average_margin']);
    }

    public function test_gets_distinct_statuses(): void
    {
        Job::factory()->create(['status' => 'completed']);
        Job::factory()->create(['status' => 'in_progress']);
        Job::factory()->create(['status' => 'completed']); // Duplicate
        Job::factory()->create(['status' => 'pending']);

        $metadata = $this->service->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('statuses', $metadata);
        $this->assertArrayHasKey('job_types', $metadata);

        $statuses = $metadata['statuses'];
        $this->assertCount(3, $statuses);
        $this->assertContains('completed', $statuses);
        $this->assertContains('in_progress', $statuses);
        $this->assertContains('pending', $statuses);

        // Verify they are ordered
        $this->assertEquals(['completed', 'in_progress', 'pending'], $statuses);
    }

    public function test_gets_distinct_job_types(): void
    {
        Job::factory()->create(['job_type' => 'Active Soil Depressurization']);
        Job::factory()->create(['job_type' => 'Radon Testing Only']);
        Job::factory()->create(['job_type' => 'Active Soil Depressurization']); // Duplicate

        $metadata = $this->service->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('job_types', $metadata);

        $jobTypes = $metadata['job_types'];
        $this->assertCount(2, $jobTypes);
        $this->assertContains('Active Soil Depressurization', $jobTypes);
        $this->assertContains('Radon Testing Only', $jobTypes);

        // Verify they are ordered alphabetically
        $this->assertEquals(['Active Soil Depressurization', 'Radon Testing Only'], $jobTypes);
    }
}
