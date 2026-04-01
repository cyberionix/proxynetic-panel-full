<?php

namespace Database\Seeders;

use App\Models\UserGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserGroup::create(['name' => 'Group 1']);
        UserGroup::create(['name' => 'Group 2']);
        UserGroup::create(['name' => 'Group 3']);
        UserGroup::create(['name' => 'Group 4']);
    }
}
