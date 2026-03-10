<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Permission::create(['name' => 'gestionar libros']);
        Permission::create(['name' => 'realizar prestamos']);

        $bibliotecario = Role::create(['name' => 'bibliotecario']);
        $bibliotecario->givePermissionTo('gestionar libros');

        $estudiante = Role::create(['name' => 'estudiante']);
        $estudiante->givePermissionTo('realizar prestamos');

        $docente = Role::create(['name' => 'docente']);
        $docente->givePermissionTo('realizar prestamos');
    }
}