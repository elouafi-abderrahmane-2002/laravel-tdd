<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('users')->delete();
        DB::table('users')->insert([
            'name' => str_random(10),
            'email' => 'tantanium@live.co.uk',
            'password' => '$2y$10$1gOJK4TGTzFqL6L7oyL6SuJHGPeIwv6q0AxXLFf/65OLGdejpfawq',
        ]);

    }
}
