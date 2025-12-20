<?php

namespace Database\Factories;

use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    public function definition(): array
    {
        $materials = [
            ['name' => 'RadonAway RP145 Fan', 'min' => 200, 'max' => 350],
            ['name' => 'RadonAway RP380 Fan', 'min' => 300, 'max' => 450],
            ['name' => '3" PVC Piping (50ft)', 'min' => 40, 'max' => 80],
            ['name' => '4" PVC Piping (50ft)', 'min' => 50, 'max' => 100],
            ['name' => 'U-Tube Manometer', 'min' => 25, 'max' => 40],
            ['name' => 'Digital Manometer', 'min' => 80, 'max' => 150],
            ['name' => 'Warning Device', 'min' => 30, 'max' => 60],
            ['name' => 'Radon Test Kit (2-pack)', 'min' => 25, 'max' => 45],
            ['name' => '6mil Poly Sheeting (1000sqft)', 'min' => 100, 'max' => 200],
            ['name' => '10mil Poly Sheeting (1000sqft)', 'min' => 150, 'max' => 250],
            ['name' => 'Seam Tape (200ft)', 'min' => 30, 'max' => 50],
            ['name' => 'Electrical Box & Wiring', 'min' => 40, 'max' => 80],
            ['name' => 'PVC Cement & Primer', 'min' => 15, 'max' => 30],
            ['name' => 'Caulk & Sealant', 'min' => 20, 'max' => 40],
            ['name' => 'Pipe Hangers & Straps', 'min' => 25, 'max' => 50],
        ];

        $material = $materials[array_rand($materials)];
        $cost = rand($material['min'] * 100, $material['max'] * 100) / 100;

        return [
            'job_id' => Job::factory(),
            'name' => $material['name'],
            'cost' => $cost,
        ];
    }
}
