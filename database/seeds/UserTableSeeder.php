<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Admin;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::firstOrNew(['email' => 'nataliia@shapelab.ee', 'name' => 'nataliia']);
        $user->password   = bcrypt('secret');
        $user->save();

        // $user = User::firstOrNew(['email' => 'bare-buddy@barebuddy.com']);
        // $user->password   = bcrypt('secret');
        // $user->save();

        // $user = Admin::firstOrNew(['email' => 'lemaik@gmx.net']);
        // $user->password   = bcrypt('secret');
        // $user->save();
    }
}
