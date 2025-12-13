<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    public function definition(): array
    {
        $jobTypes = [
            ['type' => 'Active Soil Depressurization', 'base_price' => 1200, 'base_hours' => 5],
            ['type' => 'Passive Soil Depressurization', 'base_price' => 900, 'base_hours' => 4],
            ['type' => 'Sub-Slab Depressurization', 'base_price' => 1800, 'base_hours' => 8],
            ['type' => 'Sub-Membrane Depressurization', 'base_price' => 2000, 'base_hours' => 10],
            ['type' => 'Drain Tile Suction', 'base_price' => 1500, 'base_hours' => 6],
            ['type' => 'Sump Pump Integration', 'base_price' => 1600, 'base_hours' => 7],
            ['type' => 'Crawl Space Encapsulation', 'base_price' => 3200, 'base_hours' => 16],
            ['type' => 'Vapor Barrier Installation', 'base_price' => 2400, 'base_hours' => 12],
            ['type' => 'Radon Testing Only', 'base_price' => 350, 'base_hours' => 2],
            ['type' => 'Post-Mitigation Testing', 'base_price' => 275, 'base_hours' => 1],
        ];

        $jobType = $jobTypes[array_rand($jobTypes)];

        $laborRate = rand(4500, 5500) / 100;
        $laborHours = $jobType['base_hours'] + rand(-1, 2);
        $invoiceAmount = $jobType['base_price'] + rand(-200, 400);

        $rand = rand(1, 100);
        if ($rand <= 85) {
            $status = 'completed';
        } elseif ($rand <= 95) {
            $status = 'in_progress';
        } else {
            $status = 'pending';
        }

        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
        $suffixes = ['Residence', 'Property', 'Home', 'Estate', 'Building'];

        return [
            'job_type' => $jobType['type'],
            'client_name' => $lastNames[array_rand($lastNames)] . ' ' . $suffixes[array_rand($suffixes)],
            'invoice_amount' => $invoiceAmount,
            'labor_hours' => max(1, $laborHours),
            'labor_rate' => $laborRate,
            'status' => $status,
        ];
    }
}
