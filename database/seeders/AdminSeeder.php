<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::insert([
            [
                'firstname' => 'Elyse',
                'lastname'  => 'Niyonkuru',
                'gender'    => 'male',
                'email'     => 'teclagroupltd@gmail.com',
                'phone'     => '0785389001',
                'image'     => 'user.png',
                'dob' => '2000-12-20',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'password'  => Hash::make('bugarama'),
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
        ]);
        
    }
}
