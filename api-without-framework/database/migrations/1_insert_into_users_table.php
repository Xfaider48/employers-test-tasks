<?php

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

class InsertIntoUsersTable
{
    public function up()
    {
        Manager::table('users')->insert([
            [
                'login' => 'admin',
                'name' => 'Test user'
            ]
        ]);
    }
}