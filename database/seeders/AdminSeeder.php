<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $user = User::create([
        	'username' => 'Admin',
        	'email' => 'admin@gmail.com',
        	'password' =>Hash::make('12345678'),
        ]);

        $roleAdmin = Role::create(['name' => 'admin','guard_name' => 'web']);
        $roleUser = Role::create(['name' => 'user','guard_name' => 'web']);
        $permissions = Permission::pluck('id','id')->all();
        $roleAdmin->syncPermissions($permissions);
        $user->assignRole([$roleAdmin->id]);
    }
}
