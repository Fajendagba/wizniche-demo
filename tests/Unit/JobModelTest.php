<?php

namespace Tests\Unit;

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_labor_cost_accessor_calculates_correctly(): void
    {
        $job = Job::factory()->create([
            'labor_hours' => 10,
            'labor_rate' => 52.50
        ]);

        // labor_cost = 10 * 52.50 = 525.00
        $this->assertEquals(525.00, $job->labor_cost);
    }

    public function test_labor_cost_accessor_rounds_to_two_decimals(): void
    {
        $job = Job::factory()->create([
            'labor_hours' => 7,
            'labor_rate' => 33.33
        ]);

        // labor_cost = 7 * 33.33 = 233.31 (rounded from 233.31)
        $this->assertEquals(233.31, $job->labor_cost);
    }

    public function test_completed_scope_filters_completed_jobs(): void
    {
        Job::factory()->count(3)->create(['status' => 'completed']);
        Job::factory()->count(2)->create(['status' => 'in_progress']);
        Job::factory()->create(['status' => 'pending']);

        $completedJobs = Job::completed()->get();

        $this->assertCount(3, $completedJobs);
        foreach ($completedJobs as $job) {
            $this->assertEquals('completed', $job->status);
        }
    }

    public function test_in_progress_scope_filters_in_progress_jobs(): void
    {
        Job::factory()->count(3)->create(['status' => 'completed']);
        Job::factory()->count(2)->create(['status' => 'in_progress']);
        Job::factory()->create(['status' => 'pending']);

        $inProgressJobs = Job::inProgress()->get();

        $this->assertCount(2, $inProgressJobs);
        foreach ($inProgressJobs as $job) {
            $this->assertEquals('in_progress', $job->status);
        }
    }

    public function test_search_scope_filters_by_job_type(): void
    {
        Job::factory()->create(['job_type' => 'Active Soil Depressurization', 'client_name' => 'Smith Home']);
        Job::factory()->create(['job_type' => 'Active Soil Depressurization', 'client_name' => 'Jones Home']);
        Job::factory()->create(['job_type' => 'Radon Testing Only', 'client_name' => 'Brown Home']);
        Job::factory()->create(['job_type' => 'Passive Soil Depressurization', 'client_name' => 'Wilson Home']);

        $jobs = Job::search('Active')->get();

        $this->assertCount(2, $jobs);
        foreach ($jobs as $job) {
            $this->assertStringStartsWith('Active', $job->job_type);
        }
    }

    public function test_search_scope_filters_by_client_name(): void
    {
        Job::factory()->create(['job_type' => 'Active Soil Depressurization', 'client_name' => 'Smith Home']);
        Job::factory()->create(['job_type' => 'Radon Testing Only', 'client_name' => 'Smith Property']);
        Job::factory()->create(['job_type' => 'Passive Soil Depressurization', 'client_name' => 'Johnson Home']);

        $jobs = Job::search('Smith')->get();

        $this->assertCount(2, $jobs);
        foreach ($jobs as $job) {
            $this->assertStringStartsWith('Smith', $job->client_name);
        }
    }

    public function test_search_scope_returns_all_when_search_is_empty(): void
    {
        Job::factory()->count(5)->create();

        $jobs = Job::search('')->get();

        $this->assertCount(5, $jobs);

        $jobsNull = Job::search(null)->get();

        $this->assertCount(5, $jobsNull);
    }

    public function test_filter_scope_filters_by_status(): void
    {
        Job::factory()->count(3)->create(['status' => 'completed']);
        Job::factory()->count(2)->create(['status' => 'in_progress']);

        $jobs = Job::filter(['status' => 'completed'])->get();

        $this->assertCount(3, $jobs);
        foreach ($jobs as $job) {
            $this->assertEquals('completed', $job->status);
        }
    }

    public function test_filter_scope_filters_by_job_type(): void
    {
        Job::factory()->count(4)->create(['job_type' => 'Active Soil Depressurization']);
        Job::factory()->count(2)->create(['job_type' => 'Radon Testing Only']);

        $jobs = Job::filter(['job_type' => 'Active Soil Depressurization'])->get();

        $this->assertCount(4, $jobs);
        foreach ($jobs as $job) {
            $this->assertEquals('Active Soil Depressurization', $job->job_type);
        }
    }

    public function test_filter_scope_filters_by_both_status_and_job_type(): void
    {
        Job::factory()->create(['status' => 'completed', 'job_type' => 'Active Soil Depressurization']);
        Job::factory()->create(['status' => 'completed', 'job_type' => 'Active Soil Depressurization']);
        Job::factory()->create(['status' => 'in_progress', 'job_type' => 'Active Soil Depressurization']);
        Job::factory()->create(['status' => 'completed', 'job_type' => 'Radon Testing Only']);

        $jobs = Job::filter([
            'status' => 'completed',
            'job_type' => 'Active Soil Depressurization'
        ])->get();

        $this->assertCount(2, $jobs);
        foreach ($jobs as $job) {
            $this->assertEquals('completed', $job->status);
            $this->assertEquals('Active Soil Depressurization', $job->job_type);
        }
    }
}
