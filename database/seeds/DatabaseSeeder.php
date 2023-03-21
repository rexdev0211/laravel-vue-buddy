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
        $this->call([
            EventMembersSeeder::class,
            MessageSeeder::class,
            GroupMessageSeeder::class
        ]);
    }
}
