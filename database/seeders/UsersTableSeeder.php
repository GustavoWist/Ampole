<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create multiple users

        DB::table('users')->insert([[
            'username' => 'gustavo123',
            'password' => bcrypt('gu12345'),
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'username' => 'joão123',
            'password' => bcrypt('joao12345'),
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'username' => 'guilherme123',
            'password' => bcrypt('gui12345'),
            'created_at' => date('Y-m-d H:i:s')
        ]]);
        
    }
}

