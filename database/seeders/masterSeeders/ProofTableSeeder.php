<?php

namespace Database\Seeders\masterSeeders;

use App\Models\Proof;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProofTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['proof_name' => 'Aadhar Card', 'created_by' => 1],
            ['proof_name' => 'Driving Licence', 'created_by' => 1],
            ['proof_name' => 'Leaving Certificate', 'created_by' => 1],
            ['proof_name' => 'Passport', 'created_by' => 1],
            ['proof_name' => 'PAN Card', 'created_by' => 1],
            ['proof_name' => 'Water ID', 'created_by' => 1],
        ];

        foreach ($data as $row) {
            Proof::create($row);
        }
    }
}
