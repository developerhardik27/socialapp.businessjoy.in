<?php

namespace Database\Seeders\individual;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $types = array(
            array('id' => '1', 'name' => 'hiring', 'category' => 'job', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '2', 'name' => 'looking', 'category' => 'job', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '3', 'name' => 'yearly', 'category' => 'event', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '4', 'name' => 'medical ', 'category' => 'application ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '5', 'name' => 'education ', 'category' => 'application ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
         );
        $chunks = array_chunk($types, 5);

        foreach ($chunks as $chunk) {
            DB::table('types')->insert($chunk);
        }
    }
}
