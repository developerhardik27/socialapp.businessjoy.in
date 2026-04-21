<?php

namespace Database\Seeders\individual;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $status = array(
            array('id' => '1', 'name' => 'upcoming', 'category' => 'event', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '2', 'name' => 'ongoing', 'category' => 'event', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '3', 'name' => 'complete', 'category' => 'event', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '4', 'name' => 'cancelled', 'category' => 'event', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '5', 'name' => 'pending', 'category' => 'application ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '6', 'name' => 'reject', 'category' => 'application', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '7', 'name' => 'approve', 'category' => 'application', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
          );
        $chunks = array_chunk($status, 5);

        foreach ($chunks as $chunk) {
            DB::table('status')->insert($chunk);
        }
    }
}
