<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\Material;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RadonJobsSeeder extends Seeder
{
    public function run(): void
    {
        $materialPool = [
            ['name' => 'RadonAway RP145 Fan', 'cost' => 180],
            ['name' => 'RadonAway RP380 Fan', 'cost' => 245],
            ['name' => 'RadonAway RP260 Fan', 'cost' => 210],
            ['name' => 'RadonAway GP501 Fan', 'cost' => 165],
            ['name' => '3" PVC Piping (50ft)', 'cost' => 68],
            ['name' => '3" PVC Piping (75ft)', 'cost' => 95],
            ['name' => '4" PVC Piping (40ft)', 'cost' => 68],
            ['name' => '4" PVC Piping (50ft)', 'cost' => 85],
            ['name' => 'U-Tube Manometer', 'cost' => 35],
            ['name' => 'Digital Manometer', 'cost' => 75],
            ['name' => 'Warning Device', 'cost' => 55],
            ['name' => 'Radon Test Kit', 'cost' => 25],
            ['name' => 'AirThings Corentium Monitor', 'cost' => 120],
            ['name' => 'RadStar Continuous Monitor', 'cost' => 80],
            ['name' => 'Sun Nuclear 1028 Monitor', 'cost' => 95],
            ['name' => '6mil Poly Sheeting (1000sqft)', 'cost' => 160],
            ['name' => '6mil Poly Sheeting (2000sqft)', 'cost' => 320],
            ['name' => '12mil Reinforced Vapor Barrier (1500sqft)', 'cost' => 405],
            ['name' => '12mil Reinforced Vapor Barrier (3000sqft)', 'cost' => 540],
            ['name' => 'Seam Tape (200ft)', 'cost' => 30],
            ['name' => 'Seam Tape (300ft)', 'cost' => 45],
            ['name' => 'Anchor Stakes (50pc)', 'cost' => 35],
            ['name' => 'Anchor Stakes (100pc)', 'cost' => 60],
            ['name' => 'Electrical Box & Wiring', 'cost' => 65],
            ['name' => 'Flexible Coupling', 'cost' => 28],
            ['name' => 'Drain Tile Adapter', 'cost' => 42],
            ['name' => 'Sump Pump Cover Kit', 'cost' => 95],
            ['name' => 'Spray Foam Insulation', 'cost' => 280],
            ['name' => 'Dehumidifier Unit', 'cost' => 350],
            ['name' => 'Drainage Matting', 'cost' => 180],
            ['name' => 'Foundation Sealant', 'cost' => 45],
            ['name' => 'Concrete Patch Kit', 'cost' => 32],
        ];

        $now = now();
        $jobs = [];
        $materials = [];

        for ($i = 0; $i < 50000; $i++) {
            $jobId = (string) Str::ulid();
            $jobs[] = array_merge(
                Job::factory()->raw(),
                [
                    'id' => $jobId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $numMaterials = rand(2, 5);
            for ($j = 0; $j < $numMaterials; $j++) {
                $material = $materialPool[array_rand($materialPool)];
                $materials[] = [
                    'id' => (string) Str::ulid(),
                    'job_id' => $jobId,
                    'name' => $material['name'],
                    'cost' => $material['cost'] + rand(-10, 20),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($jobs, 500) as $chunk) {
            Job::insert($chunk);
        }

        foreach (array_chunk($materials, 1000) as $chunk) {
            Material::insert($chunk);
        }
    }
}
