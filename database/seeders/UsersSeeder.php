<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تم الإضافة: التأكد من وجود الأدوار في حالة عدم تشغيل RolesSeeder مسبقاً
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // حساب أدمن (الدكتور)
        $admin = User::firstOrCreate(
            ['email' => 'admin@betscornar.com'],
            [
                'name' => 'الدكتور',
                'password' => Hash::make('admin123'),
            ]
        );
        $admin->syncRoles([$adminRole]);

        // حساب موظف (الموظف)
        $employee = User::firstOrCreate(
            ['email' => 'employee@betscornar.com'],
            [
                'name' => 'الموظف',
                'password' => Hash::make('employee123'),
            ]
        );
        $employee->syncRoles([$employeeRole]);
    }
}
