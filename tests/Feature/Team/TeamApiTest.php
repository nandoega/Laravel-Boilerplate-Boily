<?php

namespace Tests\Feature\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeamApiTest extends TestCase
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
    public function block_unauthorized_users_from_listing_teams()
    {
        $this->getJson('/api/v1/teams')->assertStatus(401);
    }
    
    /** @test */
    public function block_standard_users_from_deleting_teams()
    {
        $team = Team::factory()->create();
        $this->actingAs($this->normalUser, 'sanctum')->deleteJson('/api/v1/teams/' . $team->id)->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_a_team()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/teams', [
            'name' => 'Engineering',
            'description' => 'The dev team.'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Engineering');

        $this->assertDatabaseHas('teams', ['name' => 'Engineering']);
    }

    /** @test */
    public function missing_team_name_throws_validation_error()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/teams', [
            'description' => 'No name'
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function admin_can_sync_members_to_a_team()
    {
        $team = Team::factory()->create();
        $users = User::factory()->count(2)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson("/api/v1/teams/{$team->id}/members", [
            'user_ids' => $users->pluck('id')->toArray()
        ]);

        $response->assertStatus(200)->assertJsonFragment(['message' => 'Members attached successfully']);
        
        $this->assertCount(2, $team->fresh()->users);
    }

    /** @test */
    public function invalid_user_ids_thrown_error_on_member_assignment()
    {
        $team = Team::factory()->create();
        
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson("/api/v1/teams/{$team->id}/members", [
            'user_ids' => [99999, 88888]
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['user_ids.0', 'user_ids.1']);
    }

    /** @test */
    public function team_can_be_deleted()
    {
        $team = Team::factory()->create();
        $this->actingAs($this->adminUser, 'sanctum')
             ->deleteJson("/api/v1/teams/{$team->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }
}
