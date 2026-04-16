<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\masterSeeders\CityTableSeeder;
use Database\Seeders\masterSeeders\LeadStageSeeder;
use Database\Seeders\masterSeeders\LeadStatusSeeder;
use Database\Seeders\masterSeeders\ProofTableSeeder;
use Database\Seeders\masterSeeders\StateTableSeeder;
use Database\Seeders\masterSeeders\UsersTableSeeder;
use Database\Seeders\masterSeeders\CompanyTableSeeder;
use Database\Seeders\masterSeeders\CountryTableSeeder;
use Database\Seeders\masterSeeders\CurrencyTableSeeder;
use Database\Seeders\masterSeeders\User_roleTableSeeder;
use Database\Seeders\masterSeeders\Company_detailsTableSeeder;
use Database\Seeders\masterSeeders\User_permissionsTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            CityTableSeeder::class,
            Company_detailsTableSeeder::class,
            CompanyTableSeeder::class,
            CountryTableSeeder::class,
            CurrencyTableSeeder::class,
            LeadStageSeeder::class,
            LeadStatusSeeder::class,
            ProofTableSeeder::class,
            StateTableSeeder::class,
            User_permissionsTableSeeder::class,
            User_roleTableSeeder::class,
            UsersTableSeeder::class,
        ]);
    }
}
