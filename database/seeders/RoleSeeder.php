<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::query()->create(['name' => 'Super Admin', 'guard_name' => 'web']);
        //Role::factory()->count(10)->create();
    }
}
