<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use Spatie\Permission\Models\Role;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'bibliotecario']);
        Role::create(['name' => 'estudiante']);
    }

    public function test_cualquier_usuario_puede_ver_el_catalogo_de_libros()
    {
        $user = User::factory()->create();
        Book::factory()->count(3)->create(); 

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/books');

        $response->assertStatus(200);
    }

    public function test_estudiante_no_puede_crear_libro_y_recibe_error_403()
    {
        $estudiante = User::factory()->create()->assignRole('estudiante');

        $response = $this->actingAs($estudiante, 'sanctum')->postJson('/api/v1/books', [
            'title' => 'Libro Hackeado',
            'description' => 'Descripción pirata',
            'ISBN' => '123456789',
            'total_copies' => 1,
            'available_copies' => 1,
            'is_available' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_bibliotecario_puede_crear_libro()
    {
        $bibliotecario = User::factory()->create()->assignRole('bibliotecario');

        $response = $this->actingAs($bibliotecario, 'sanctum')->postJson('/api/v1/books', [
            'title' => 'Clean Code',
            'description' => 'A Handbook of Agile Software Craftsmanship',
            'ISBN' => '9780132350884',
            'total_copies' => 5,
            'available_copies' => 5,
            'is_available' => true, 
        ]);

        $response->assertStatus(201); 
    }
}