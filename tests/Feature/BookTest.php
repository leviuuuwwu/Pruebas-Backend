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
        \Spatie\Permission\Models\Permission::create(['name' => 'gestionar libros']);
        \Spatie\Permission\Models\Permission::create(['name' => 'realizar prestamos']);
        
        Role::create(['name' => 'bibliotecario'])->givePermissionTo('gestionar libros');
        Role::create(['name' => 'estudiante'])->givePermissionTo('realizar prestamos');
        Role::create(['name' => 'docente'])->givePermissionTo('realizar prestamos');
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

    public function test_cualquier_usuario_puede_ver_el_detalle_de_un_libro()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
    }

    public function test_estudiante_no_puede_actualizar_libro_y_recibe_error_403()
    {
        $estudiante = User::factory()->create()->assignRole('estudiante');
        $book = Book::factory()->create();

        $response = $this->actingAs($estudiante, 'sanctum')->putJson("/api/v1/books/{$book->id}", [
            'title' => 'Titulo Modificado',
        ]);

        $response->assertStatus(403);
    }

    public function test_bibliotecario_puede_actualizar_libro()
    {
        $bibliotecario = User::factory()->create()->assignRole('bibliotecario');
        $book = Book::factory()->create();

        $response = $this->actingAs($bibliotecario, 'sanctum')->putJson("/api/v1/books/{$book->id}", [
            'title' => 'Titulo Actualizado Oficialmente',
        ]);

        $response->assertStatus(200);
    }

    public function test_estudiante_no_puede_eliminar_libro_y_recibe_error_403()
    {
        $estudiante = User::factory()->create()->assignRole('estudiante');
        $book = Book::factory()->create();

        $response = $this->actingAs($estudiante, 'sanctum')->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(403);
    }

    public function test_bibliotecario_puede_eliminar_libro()
    {
        $bibliotecario = User::factory()->create()->assignRole('bibliotecario');
        $book = Book::factory()->create();

        $response = $this->actingAs($bibliotecario, 'sanctum')->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
    }

    public function test_error_422_al_crear_libro_con_informacion_incompleta()
    {
        $bibliotecario = User::factory()->create()->assignRole('bibliotecario');

       
        $response = $this->actingAs($bibliotecario, 'sanctum')->postJson('/api/v1/books', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'description', 'ISBN']);
    }

    public function test_error_401_al_intentar_crear_libro_sin_sesion()
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => 'Libro Fantasma',
        ]);

        $response->assertStatus(401); 
        }

    public function test_usuario_puede_filtrar_libros_por_titulo()
    {
        $user = User::factory()->create();
        
        Book::factory()->create(['title' => 'El Señor de los Anillos']);

        Book::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/books?title=Anillos');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'El Señor de los Anillos'])
                 ->assertJsonCount(1); 
    }

    public function test_error_404_al_ver_detalle_de_libro_inexistente()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/books/9999');

        $response->assertStatus(404); 
    }
}