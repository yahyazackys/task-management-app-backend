<?php

namespace Database\Seeders;

use App\Models\CategoryTask;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CategoryTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CategoryTask::create([
            'name_category' => 'Priority Task',
        ]);
        CategoryTask::create([
            'name_category' => 'Daily Task',
        ]);
    }
}
