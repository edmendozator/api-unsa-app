<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        Admin::create([
            'email' => 'flx@unsa.edu.pe'
        ]);
        Admin::create([
            'email' => 'flx@unsa.edu.pe'
        ]);
        Admin::create([
            'email' => 'kbegazo@unsa.edu.pe'
        ]);
        Admin::create([
            'email' => 'plazo@unsa.edu.pe'
        ]);
        Admin::create([
            'email' => 'jmolina@unsa.edu.pe'
        ]);
        Admin::create([
            'email' => 'soporte02@unsa.edu.pe'
        ]);
    }
}
