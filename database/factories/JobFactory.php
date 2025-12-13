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

        $jobType = $this->faker->randomElement($jobTypes);

        $laborRate = $this->faker->randomFloat(2, 45, 55);
        $laborHours = $jobType['base_hours'] + $this->faker->numberBetween(-1, 2);
        $invoiceAmount = $jobType['base_price'] + $this->faker->numberBetween(-200, 400);

        $statuses = ['completed' => 85, 'in_progress' => 10, 'pending' => 5];
        $status = $this->faker->randomElement(
            array_merge(
                array_fill(0, $statuses['completed'], 'completed'),
                array_fill(0, $statuses['in_progress'], 'in_progress'),
                array_fill(0, $statuses['pending'], 'pending')
            )
        );

        return [
            'job_type' => $jobType['type'],
            'client_name' => $this->faker->lastName() . ' ' . $this->faker->randomElement(['Residence', 'Property', 'Home', 'Estate', 'Building']),
            'invoice_amount' => $invoiceAmount,
            'labor_hours' => max(1, $laborHours),
            'labor_rate' => $laborRate,
            'status' => $status,
        ];
    }
}
