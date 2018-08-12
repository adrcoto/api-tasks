<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->name = "Admin";
        $user->email = "admin@tasks.com";
        $user->password = Hash::make("admin");
        $user->status = User::STATUS_ACTIVE;
        $user->role_id = Role::ROLE_ADMIN;

        $user->save();
    }
}
