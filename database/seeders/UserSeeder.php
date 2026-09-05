<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        DB::table('users')->updateOrInsert(
            ['username' => 'supervisor1'],
            [
                'password' => Hash::make('supervisor123'),
                'role'     => 'supervisor',
            ]
        );

        DB::table('users')->updateOrInsert(
            ['username' => 'satpam1'],
            [
                'password' => Hash::make('satpam123'),
                'role'     => 'satpam',
            ]
        );
    }
}
