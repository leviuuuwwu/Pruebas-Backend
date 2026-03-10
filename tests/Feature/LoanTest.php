<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use Spatie\Permission\Models\Role;

class LoanTest extends TestCase
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

    public function test_bibliotecario_no_puede_prestar_un_libro_y_recibe_error_403()
    {
        $bibliotecario = User::factory()->create()->assignRole('bibliotecario');
        
        $book = Book::factory()->create([
            'total_copies' => 5,
            'available_copies' => 5,
            'is_available' => true,
        ]);

        $response = $this->actingAs($bibliotecario, 'sanctum')->postJson('/api/v1/loans', [
            'requester_name' => $bibliotecario->name,
            'book_id' => $book->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_estudiante_puede_prestar_un_libro()
    {
        $estudiante = User::factory()->create()->assignRole('estudiante');
        
        $book = Book::factory()->create([
            'total_copies' => 5,
            'available_copies' => 5,
            'is_available' => true,
        ]);

        $response = $this->actingAs($estudiante, 'sanctum')->postJson('/api/v1/loans', [
            'requester_name' => $estudiante->name,
            'book_id' => $book->id,
        ]);

        $response->assertStatus(201);
    }

    public function test_cualquier_usuario_puede_ver_historial_de_prestamos()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/loans');

        $response->assertStatus(200);
    }

    public function test_usuario_puede_devolver_un_libro()
    {
        $estudiante = User::factory()->create()->assignRole('estudiante');
        
        $book = Book::factory()->create([
            'total_copies' => 5,
            'available_copies' => 4,
            'is_available' => true,
        ]);

        $loan = \App\Models\Loan::create([
            'requester_name' => $estudiante->name,
            'book_id' => $book->id,
            'user_id' => $estudiante->id, 
        ]);

        $response = $this->actingAs($estudiante, 'sanctum')->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(200);
    }

    public function test_error_422_al_intentar_devolver_prestamo_ya_devuelto()
    {
        $estudiante = User::factory()->create()->assignRole('estudiante');
        $book = Book::factory()->create();

        $loan = \App\Models\Loan::create([
            'requester_name' => $estudiante->name,
            'book_id' => $book->id,
            'user_id' => $estudiante->id, 
            'return_at' => now(),
        ]);

        $response = $this->actingAs($estudiante, 'sanctum')->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(422);
    }

    public function test_estudiante_no_puede_devolver_el_prestamo_de_otro_estudiante_y_recibe_403()
    {
        $estudiante1 = User::factory()->create()->assignRole('estudiante');
        $estudiante2 = User::factory()->create()->assignRole('estudiante'); // El intruso
        
        $book = Book::factory()->create();

        $loan = \App\Models\Loan::create([
            'requester_name' => $estudiante1->name,
            'book_id' => $book->id,
            'user_id' => $estudiante1->id,
        ]);

        $response = $this->actingAs($estudiante2, 'sanctum')->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(403);
    }

    public function test_bibliotecario_puede_devolver_prestamo_de_cualquier_estudiante()
    {
        $estudiante = User::factory()->create()->assignRole('estudiante');
        $bibliotecario = User::factory()->create()->assignRole('bibliotecario');
        
        $book = Book::factory()->create([
            'total_copies' => 5,
            'available_copies' => 4,
            'is_available' => true,
        ]);

        $loan = \App\Models\Loan::create([
            'requester_name' => $estudiante->name,
            'book_id' => $book->id,
            'user_id' => $estudiante->id,
        ]);
        $response = $this->actingAs($bibliotecario, 'sanctum')->postJson("/api/v1/loans/{$loan->id}/return");
        $response->assertStatus(200);
    }
}