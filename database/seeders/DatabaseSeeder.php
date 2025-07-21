<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $studentRole = Role::create(['name' => 'student']);
        $adminRole = Role::create(['name' => 'admin']);
        
        // Crear algunos permisos (opcional)
        $viewDashboard = Permission::create(['name' => 'view dashboard']);
        $registerApprovedSubjects = Permission::create(['name' => 'register approved subjects']);
        $registerPlannedSubjects = Permission::create(['name' => 'register planned subjects']);
        $viewApprovedSubjects = Permission::create(['name' => 'view approved subjects']);
        $viewAvailableSubjects = Permission::create(['name' => 'view available subjects']);
        $viewPlannedSubjects = Permission::create(['name' => 'view planned subjects']);
        
        $registerPeriods = Permission::create(['name' => 'register periods']);
        $viewPeriods = Permission::create(['name' => 'view periods']);
        $viewSubjectsDemand = Permission::create(['name' => 'view subjects demand']);
        
        
        // Asignar permisos a roles
        $studentRole->givePermissionTo($viewDashboard, $registerApprovedSubjects, $registerPlannedSubjects, $viewApprovedSubjects, $viewAvailableSubjects, $viewPlannedSubjects);
        $adminRole->givePermissionTo($viewDashboard, $registerPeriods, $viewPeriods, $viewSubjectsDemand);

        // Crear usuario estudiante
        $student = User::create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => bcrypt('testStudent123'),
        ]);
        $student->assignRole($studentRole);

        // Crear usuario admin
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('testAmind123'),
        ]);
        $admin->assignRole($adminRole);
    }
}