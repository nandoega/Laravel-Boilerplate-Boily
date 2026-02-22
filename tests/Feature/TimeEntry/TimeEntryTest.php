<?php

namespace Tests\Feature\TimeEntry;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $normalUser;
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole('user');
        
        $project = Project::factory()->create();
        $this->task = Task::factory()->create(['project_id' => $project->id]);
    }

    /** @test */
    public function users_must_be_authenticated_to_manage_time()
    {
        $this->postJson('/api/v1/time-entries', [])->assertStatus(401);
    }
    
    /** @test */
    public function creating_time_entry_requires_task_id_date_and_hours()
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')->postJson('/api/v1/time-entries', [
            'description' => 'Did some work'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_id', 'date', 'hours']);
    }

    /** @test */
    public function user_can_log_time_for_a_task()
    {
        $response = $this->actingAs($this->normalUser, 'sanctum')->postJson('/api/v1/time-entries', [
            'task_id' => $this->task->id,
            'date' => '2025-05-15',
            'hours' => 3.5,
            'description' => 'Developed login form',
            'is_billable' => true,
            'hourly_rate' => 50
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.hours', 3.5)
                 ->assertJsonPath('data.is_billable', true);

        $this->assertDatabaseHas('time_entries', [
            'task_id' => $this->task->id,
            'user_id' => $this->normalUser->id,
            'hours' => 3.5
        ]);
    }

    /** @test */
    public function user_can_delete_their_own_time_entry()
    {
        $entry = TimeEntry::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->normalUser->id
        ]);

        $response = $this->actingAs($this->normalUser, 'sanctum')->deleteJson('/api/v1/time-entries/' . $entry->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }
    
    /** @test */
    public function admins_can_delete_any_time_entry()
    {
        $entry = TimeEntry::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => clone $this->normalUser->id // fake different ID
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->deleteJson('/api/v1/time-entries/' . $entry->id);
        $response->assertStatus(200);
    }
}
