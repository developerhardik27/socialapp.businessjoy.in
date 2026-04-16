<?php

namespace Database\Seeders\individual;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class letter_variable_settingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $letter_variable_settings = array(
            array('id' => '1', 'variable' => '$fname', 'employee_fields' => 'first_name', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '2', 'variable' => '$mname', 'employee_fields' => 'middle_name', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '3', 'variable' => '$sname', 'employee_fields' => 'surname', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '4', 'variable' => '$email ', 'employee_fields' => 'email ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '5', 'variable' => '$mobile ', 'employee_fields' => 'mobile ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '6', 'variable' => '$house_no ', 'employee_fields' => 'house_no_building_name ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '7', 'variable' => '$road_area_name ', 'employee_fields' => 'road_name_area_colony ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '8', 'variable' => '$city_name ', 'employee_fields' => 'city_id ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '9', 'variable' => '$state_name ', 'employee_fields' => 'state_id ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0'),
            array('id' => '10', 'variable' => '$pincode ', 'employee_fields' => 'pincode ', 'created_by' => '1', 'updated_by' => NULL, 'created_at' => NULL, 'updated_at' => NULL, 'is_active' => '1', 'is_deleted' => '0')
        );
        $chunks = array_chunk($letter_variable_settings, 5);

        foreach ($chunks as $chunk) {
            DB::table('letter_variable_settings')->insert($chunk);
        }
    }
}
