<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sim;

class SimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sims = [
            ['name' => 'Baru'],
            ['name' => 'Perpanjang'],
            ['name' => 'Peningkatan'],
        ];

        foreach ($sims as $sim) {
            Sim::create($sim);
        }
    }
}