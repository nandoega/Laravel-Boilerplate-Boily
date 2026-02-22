<?php

namespace Tests\Feature\Project;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $normalUser;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole('user');
        
        $this->client = Client::factory()->create();
    }

    /** @test */
    public function unauthenticated_access_is_blocked()
    {
        $this->getJson('/api/v1/projects')->assertStatus(401);
        $this->postJson('/api/v1/projects', [])->assertStatus(401);
    }

    /** @test */
    public function standard_users_cannot_create_projects()
    {
        $this->actingAs($this->normalUser, 'sanctum')
            ->postJson('/api/v1/projects', [
                'name' => 'Test', 'client_id' => $this->client->id
            ])->assertStatus(403);
    }

    /** @test */
    public function admins_can_create_projects_with_valid_data()
    {
        $data = [
            'client_id'   => $this->client->id,
            'name'        => 'Website Revamp',
            'description' => 'A complete overhaul of the corp site.',
            'budget'      => 5000,
            'start_date'  => '2025-01-01',
            'end_date'    => '2025-06-01',
            'status'      => 'planning'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/projects', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Website Revamp')
                 ->assertJsonPath('data.budget', 5000);

        $this->assertDatabaseHas('projects', ['name' => 'Website Revamp']);
    }

    /** @test */
    public function project_requires_existing_client()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/projects', [
            'name' => 'No Client',
            'client_id' => 99999, // Does not exist
            'status' => 'planning'
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['client_id']);
    }

    /** @test */
    public function project_end_date_must_be_after_start_date()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/projects', [
            'name'       => 'Wrong Dates',
            'client_id'  => $this->client->id,
            'start_date' => '2025-06-01',
            'end_date'   => '2025-01-01', // Before start
            'status'     => 'planning'
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function can_assign_and_sync_members_to_project()
    {
        $project = Project::factory()->create(['client_id' => $this->client->id]);
        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson("/api/v1/projects/{$project->id}/members", [
            'user_ids' => $users->pluck('id')->toArray()
        ]);

        $response->assertStatus(200)->assertJsonFragment(['message' => 'Members attached successfully']);
        
        $this->assertCount(3, $project->fresh()->users);
    }
    
    /** @test */
    public function admin_can_update_project_status()
    {
        $project = Project::factory()->create(['client_id' => $this->client->id, 'status' => 'planning']);

        $response = $this->actingAs($this->adminUser, 'sanctum')->putJson("/api/v1/projects/{$project->id}", [
            'client_id' => $this->client->id,
            'name' => 'Updated Project',
            'status' => 'in_progress'
        ]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'in_progress');
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'in_progress']);
    }
    
    /** @test */
    public function deleting_a_project_cascades_safely()
    {
         $project = Project::factory()->create(['client_id' => $this->client->id]);
         
         $response = $this->actingAs($this->adminUser, 'sanctum')->deleteJson("/api/v1/projects/{$project->id}");
         
         $response->assertStatus(200);
         $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
