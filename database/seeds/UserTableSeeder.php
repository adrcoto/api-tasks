<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Hash;
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $normalRole = Role::where('name', 'normal')->first();
        $adminRole = Role::where('name', 'admin')->first();

        $administrator = new User();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@yahoo.com';
        $administrator->password = Hash::make('admin');
        $administrator->status = 1;

        $administrator->save();
        $administrator->roles()->attach($adminRole);



//        $user = new User();
//        $user->name = 'test_user';
//        $user->email = 'test_user@yahoo.com';
//        $user->password = 'orange123';
//        $user->status = 0;
//
//        $user->save();
//        $user->roles()->attach($normalRole);
    }
}
