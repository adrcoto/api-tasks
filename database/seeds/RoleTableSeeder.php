<?php

use Illuminate\Database\Seeder;
use App\Role;
class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $normalRole = new Role();
        $normalRole->name = 'normal';
        $normalRole->description = 'A normal User';
        $normalRole->save();

        $adminRole = new Role();
        $adminRole->name = 'admin';
        $adminRole->description = 'An admin User';

        $adminRole->save();
    }
}
