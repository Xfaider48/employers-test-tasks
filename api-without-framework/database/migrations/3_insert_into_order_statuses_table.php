<?php

use Illuminate\Database\Capsule\Manager;

class InsertIntoOrderStatusesTable
{
    public function up()
    {
        $time = \Carbon\Carbon::now();
        Manager::table('order_statuses')->insert([
            [
                'id' => 'new',
                'created_at' => $time,
                'updated_at' => $time,
            ],
            [
                'id' => 'paid',
                'created_at' => $time,
                'updated_at' => $time,
            ]
        ]);
    }
}