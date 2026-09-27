<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear o actualizar un usuario de pruebas sin duplicarlo
        $user = User::updateOrCreate(
            ['email' => 'admin@boletas.com'], // Condición para buscar si existe
            [
                'name' => 'jluna',
                'nombre_completo' => 'Jaime Luna',
                'password' => Hash::make('password123'), // Contraseña encriptada
            ]
        );
        $user1 = User::updateOrCreate(
            ['email' => 'adminn@boletas.com'], // Condición para buscar si existe
            [
                'name' => 'jreyes',
                'nombre_completo' => 'Yeefre Reyes',
                'password' => Hash::make('password123'), // Contraseña encriptada
            ]
        );

        $role = Role::create(['name' => 'Administrador']);
        $role1 = Role::create(['name' => 'Supervisor']);

        $user->assignRole($role);
        $user1->assignRole($role1);

    }
}
