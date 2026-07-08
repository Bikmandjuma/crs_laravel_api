<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'firstname' => 'Bikman',
                'lastname'  => 'Djuma',
                'gender'    => 'male',
                'email'     => 'bikmangeek@gmail.com',
                'country'   => 'Rwanda',
                'phone'     => '0785389000',
                'image'     => 'user.png',
                'birthdate' => '2000-12-20',
                'professionalism' => 'Developer',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'password'  => Hash::make('bugarama'),
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
        ]);

        
    }
}
