<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Insert user data into the MongoDB database
        // User::create([
        //     'name' => 'John Doe',
        //     'phone_number' => '9876543210'
        // ]);

        // User::create([
        //     'name' => 'Jane Doe',
        //     'phone_number' => '1234567890'
        // ]);

        // User::create([
        //     'name' => 'Sumudu Perera',
        //     'phone_number' => '0717865234'
        // ]);

        // User::create([
        //     'name' => 'Anjana Silva',
        //     'phone_number' => '0752345167'
        // ]);

        User::create([
            'name' => 'Saman Peris',
            'phone_number' => '076782342'
        ]);

        User::create([
            'name' => 'Kavidndu Silva',
            'phone_number' => '0723141515'
        ]);
        User::create([
            'name' => 'Naduni Weerathunga',
            'phone_number' => '076234777'
        ]);
    }
}
