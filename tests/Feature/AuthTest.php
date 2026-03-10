<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_iniciar_sesion()
    {
        $user = User::factory()->create([
            'email' => 'test@correo.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@correo.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type', 'user']); 
    }

    public function test_usuario_puede_ver_su_perfil()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/profile');

        $response->assertStatus(200);
    }

    public function test_usuario_puede_cerrar_sesion()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/logout');

        $response->assertStatus(200); 
    }

    public function test_error_al_iniciar_sesion_con_password_incorrecta()
    {
        $user = User::factory()->create([
            'email' => 'real@correo.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'real@correo.com',
            'password' => 'clave-equivocada', 
        ]);

        $response->assertStatus(422);}

    public function test_error_al_cerrar_sesion_sin_estar_autenticado()
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401); 
    }
    
    public function test_error_al_ver_perfil_sin_estar_autenticado()
    {
        $response = $this->getJson('/api/v1/profile');
        $response->assertStatus(401);
    }

    public function test_error_al_iniciar_sesion_con_email_inexistente()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'noexisto@correo.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422); 
    }
}