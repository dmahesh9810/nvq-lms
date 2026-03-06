<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = Module::all();

        foreach ($modules as $module) {
            Unit::firstOrCreate(
            [
                'module_id' => $module->id,
                'title' => 'Core Unit: ' . $module->title,
            ],
            [
                'description' => 'The main learning unit containing all practical and theoretical lessons for ' . $module->title,
                'order' => 1,
                'is_active' => true,
            ]
            );
        }
    }
}
