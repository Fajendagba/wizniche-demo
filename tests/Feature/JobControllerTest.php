<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobControllerTest extends TestCase
{
    use RefreshDatabase;

    // ========== index() endpoint tests ==========

    public function test_index_returns_paginated_jobs(): void
    {
        Job::factory()
            ->has(Material::factory()->count(2))
            ->count(15)
            ->create();

        $response = $this->getJson('/api/v1/jobs?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'job_type',
                        'client_name',
                        'invoice_amount',
                        'labor_hours',
                        'labor_rate',
                        'status',
                        'materials',
                        'labor_cost',
                        'material_cost',
                        'total_cost',
                        'gross_profit',
                        'profit_margin',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ],
                'links'
            ])
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_index_search_by_job_type(): void
    {
        Job::factory()->create(['job_type' => 'Active Soil Depressurization']);
        Job::factory()->create(['job_type' => 'Active Soil Depressurization']);
        Job::factory()->create(['job_type' => 'Radon Testing Only']);
        Job::factory()->create(['job_type' => 'Passive Soil Depressurization']);

        $response = $this->getJson('/api/v1/jobs?search=Active');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Verify all returned jobs match the search
        $data = $response->json('data');
        foreach ($data as $job) {
            $this->assertStringStartsWith('Active', $job['job_type']);
        }
    }

    public function test_index_search_by_client_name(): void
    {
        Job::factory()->create(['client_name' => 'Smith Residence']);
        Job::factory()->create(['client_name' => 'Smith Property']);
        Job::factory()->create(['client_name' => 'Johnson Home']);

        $response = $this->getJson('/api/v1/jobs?search=Smith');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Verify all returned jobs match the search
        $data = $response->json('data');
        foreach ($data as $job) {
            $this->assertStringStartsWith('Smith', $job['client_name']);
        }
    }

    public function test_index_filter_by_status(): void
    {
        Job::factory()->count(3)->create(['status' => 'completed']);
        Job::factory()->count(2)->create(['status' => 'in_progress']);
        Job::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/v1/jobs?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // Verify all returned jobs have the correct status
        $data = $response->json('data');
        foreach ($data as $job) {
            $this->assertEquals('completed', $job['status']);
        }
    }

    public function test_index_filter_by_job_type(): void
    {
        Job::factory()->count(4)->create(['job_type' => 'Active Soil Depressurization']);
        Job::factory()->count(2)->create(['job_type' => 'Radon Testing Only']);

        $response = $this->getJson('/api/v1/jobs?job_type=Active Soil Depressurization');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');

        // Verify all returned jobs have the correct job type
        $data = $response->json('data');
        foreach ($data as $job) {
            $this->assertEquals('Active Soil Depressurization', $job['job_type']);
        }
    }

    // ========== show() endpoint tests ==========

    public function test_show_returns_single_job_with_materials(): void
    {
        $job = Job::factory()->create([
            'job_type' => 'Active Soil Depressurization',
            'client_name' => 'Test Client',
            'invoice_amount' => 1000.00,
            'labor_hours' => 10,
            'labor_rate' => 50.00,
            'status' => 'completed'
        ]);

        Material::factory()->count(3)->create(['job_id' => $job->id]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'job_type',
                    'client_name',
                    'invoice_amount',
                    'labor_hours',
                    'labor_rate',
                    'status',
                    'materials' => [
                        '*' => [
                            'id',
                            'name',
                            'cost',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'labor_cost',
                    'material_cost',
                    'total_cost',
                    'gross_profit',
                    'profit_margin',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJsonPath('data.id', $job->id)
            ->assertJsonPath('data.job_type', 'Active Soil Depressurization')
            ->assertJsonPath('data.client_name', 'Test Client')
            ->assertJsonCount(3, 'data.materials');
    }

    public function test_show_returns_404_for_invalid_ulid(): void
    {
        $response = $this->getJson('/api/v1/jobs/01JFQXM8K3INVALID1234567');

        $response->assertStatus(404);
    }

    // ========== stats() endpoint tests ==========

    public function test_stats_returns_aggregated_data(): void
    {
        // Create job 1
        $job1 = Job::factory()->create([
            'invoice_amount' => 1000.00,
            'labor_hours' => 10,
            'labor_rate' => 50.00,
        ]);
        Material::factory()->create(['job_id' => $job1->id, 'cost' => 200.00]);

        // Create job 2
        $job2 = Job::factory()->create([
            'invoice_amount' => 2000.00,
            'labor_hours' => 20,
            'labor_rate' => 40.00,
        ]);
        Material::factory()->create(['job_id' => $job2->id, 'cost' => 400.00]);

        $response = $this->getJson('/api/v1/jobs/stats/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_jobs',
                    'total_revenue',
                    'total_profit',
                    'average_margin'
                ]
            ])
            ->assertJsonPath('data.total_jobs', 2);

        // Use loose comparison for float values
        $data = $response->json('data');
        $this->assertEquals(3000.00, $data['total_revenue']);
        $this->assertEquals(1100.00, $data['total_profit']); // (1000-500-200) + (2000-800-400)
        $this->assertEquals(35.00, $data['average_margin']); // (30 + 40) / 2
    }

    public function test_stats_returns_zeros_when_no_jobs(): void
    {
        $response = $this->getJson('/api/v1/jobs/stats/summary');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_jobs', 0);

        // Check that numeric values are 0 (could be 0 or 0.00)
        $data = $response->json('data');
        $this->assertEquals(0, $data['total_revenue']);
        $this->assertEquals(0, $data['total_profit']);
        $this->assertEquals(0, $data['average_margin']);
    }

    // ========== metadata() endpoint tests ==========

    public function test_metadata_returns_distinct_statuses_and_job_types(): void
    {
        Job::factory()->create(['status' => 'completed', 'job_type' => 'Active Soil Depressurization']);
        Job::factory()->create(['status' => 'in_progress', 'job_type' => 'Radon Testing Only']);
        Job::factory()->create(['status' => 'completed', 'job_type' => 'Passive Soil Depressurization']);
        Job::factory()->create(['status' => 'pending', 'job_type' => 'Active Soil Depressurization']);

        $response = $this->getJson('/api/v1/jobs/metadata');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'statuses',
                    'job_types'
                ]
            ]);

        $data = $response->json('data');

        $this->assertCount(3, $data['statuses']);
        $this->assertContains('completed', $data['statuses']);
        $this->assertContains('in_progress', $data['statuses']);
        $this->assertContains('pending', $data['statuses']);

        $this->assertCount(3, $data['job_types']);
        $this->assertContains('Active Soil Depressurization', $data['job_types']);
        $this->assertContains('Radon Testing Only', $data['job_types']);
        $this->assertContains('Passive Soil Depressurization', $data['job_types']);
    }

    public function test_metadata_returns_empty_arrays_when_no_jobs(): void
    {
        $response = $this->getJson('/api/v1/jobs/metadata');

        $response->assertStatus(200)
            ->assertJsonPath('data.statuses', [])
            ->assertJsonPath('data.job_types', []);
    }

    // ========== JobResource transformation test ==========

    public function test_job_resource_includes_profit_calculations(): void
    {
        $job = Job::factory()->create([
            'invoice_amount' => 1000.00,
            'labor_hours' => 10,
            'labor_rate' => 50.00, // labor_cost = 500
        ]);

        Material::factory()->create(['job_id' => $job->id, 'cost' => 200.00]);
        Material::factory()->create(['job_id' => $job->id, 'cost' => 100.00]);
        // material_cost = 300
        // total_cost = 500 + 300 = 800
        // gross_profit = 1000 - 800 = 200
        // profit_margin = (200 / 1000) * 100 = 20%

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertStatus(200);

        // Use loose comparison for float values
        $data = $response->json('data');
        $this->assertEquals(500.00, $data['labor_cost']);
        $this->assertEquals(300.00, $data['material_cost']);
        $this->assertEquals(800.00, $data['total_cost']);
        $this->assertEquals(200.00, $data['gross_profit']);
        $this->assertEquals(20.00, $data['profit_margin']);
    }
}
