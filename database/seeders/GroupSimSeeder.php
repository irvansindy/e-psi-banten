<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GroupSim;

class GroupSimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupSims = [
            ['name' => 'A'],
            ['name' => 'C'],
            ['name' => 'A & C'],
            ['name' => 'B1'],
            ['name' => 'B1 Umum'],
            ['name' => 'B2'],
            ['name' => 'B2 Umum'],
        ];

        foreach ($groupSims as $groupSim) {
            GroupSim::create($groupSim);
        }
    }
}