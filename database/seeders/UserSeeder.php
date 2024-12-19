<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'yahya zacky s',
            'email' => 'yahya@gmail.com',
            'profession' => 'nguli',
            'no_hp' => '0812782121',
            'password' => Hash::make('Yahya123'),
        ]);
    }
}
