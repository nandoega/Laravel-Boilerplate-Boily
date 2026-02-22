<?php

namespace Tests\Feature\Client;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $normalUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole('user');
    }

    /** @test */
    public function unauthenticated_users_cannot_access_clients_api()
    {
        $this->getJson('/api/v1/clients')->assertStatus(401);
        $this->postJson('/api/v1/clients', [])->assertStatus(401);
        $this->putJson('/api/v1/clients/1', [])->assertStatus(401);
        $this->deleteJson('/api/v1/clients/1')->assertStatus(401);
    }

    /** @test */
    public function normal_users_cannot_create_or_delete_clients()
    {
        $client = Client::factory()->create();

        $this->actingAs($this->normalUser, 'sanctum')
            ->postJson('/api/v1/clients', ['name' => 'New Client'])
            ->assertStatus(403);

        $this->actingAs($this->normalUser, 'sanctum')
            ->deleteJson("/api/v1/clients/{$client->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_list_clients_paginated()
    {
        Client::factory()->count(15)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')->getJson('/api/v1/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'company', 'address', 'created_at']
                ],
                'links',
                'meta'
            ]);
            
        $this->assertCount(10, $response->json('data')); // Default pagination is 10
    }

    /** @test */
    public function admin_can_create_client_with_valid_data()
    {
        $clientData = [
            'name' => 'Acme Corp',
            'email' => 'contact@acme.com',
            'phone' => '123456789',
            'company' => 'Acme Corporation',
            'address' => '123 Tech Lane'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/clients', $clientData);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Acme Corp');

        $this->assertDatabaseHas('clients', ['email' => 'contact@acme.com']);
    }

    /** @test */
    public function client_creation_requires_name_and_email()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/clients', [
            'phone' => '1234'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    /** @test */
    public function client_email_must_be_unique_on_creation()
    {
        Client::factory()->create(['email' => 'duplicate@acme.com']);

        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/clients', [
            'name' => 'Another Corp',
            'email' => 'duplicate@acme.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function admin_can_update_existing_client()
    {
        $client = Client::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->adminUser, 'sanctum')->putJson("/api/v1/clients/{$client->id}", [
            'name' => 'Updated Name',
            'email' => $client->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function client_email_unique_validation_ignores_itself_on_update()
    {
        $client = Client::factory()->create(['email' => 'self@acme.com']);

        $response = $this->actingAs($this->adminUser, 'sanctum')->putJson("/api/v1/clients/{$client->id}", [
            'name' => 'New Name',
            'email' => 'self@acme.com', // same email
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_delete_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')->deleteJson("/api/v1/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Client deleted successfully']);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
    
    /** @test */
    public function cannot_fetch_or_delete_non_existent_client()
    {
        $this->actingAs($this->adminUser, 'sanctum')->getJson('/api/v1/clients/9999')->assertStatus(404);
        $this->actingAs($this->adminUser, 'sanctum')->deleteJson('/api/v1/clients/9999')->assertStatus(404);
    }
}
