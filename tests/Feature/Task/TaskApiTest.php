<?php

namespace Tests\Feature\Task;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $normalUser;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');
        
        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole('user');

        $this->project = Project::factory()->create();
    }

    /** @test */
    public function standard_users_can_view_tasks()
    {
        Task::factory()->count(3)->create(['project_id' => $this->project->id]);

        $response = $this->actingAs($this->normalUser, 'sanctum')->getJson('/api/v1/tasks');

        $response->assertStatus(200)->assertJsonStructure(['data', 'links']);
    }

    /** @test */
    public function task_requires_project_id_and_title()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/tasks', [
            'priority' => 'low'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id', 'title']);
    }

    /** @test */
    public function admin_can_create_a_valid_task()
    {
        $data = [
            'project_id'  => $this->project->id,
            'title'       => 'Setup Server',
            'status'      => 'pending',
            'priority'    => 'high',
            'due_date'    => '2025-10-10'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/tasks', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Setup Server')
                 ->assertJsonPath('data.priority', 'high');
                 
        $this->assertDatabaseHas('tasks', ['title' => 'Setup Server']);
    }

    /** @test */
    public function standard_user_cannot_delete_tasks()
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $this->actingAs($this->normalUser, 'sanctum')
             ->deleteJson("/api/v1/tasks/{$task->id}")
             ->assertStatus(403);
    }
    
    /** @test */
    public function admin_can_assign_task_to_user()
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);
        $assignee = User::factory()->create();
        
        $response = $this->actingAs($this->adminUser, 'sanctum')->putJson("/api/v1/tasks/{$task->id}", [
            'project_id'  => $this->project->id,
            'title'       => $task->title,
            'status'      => $task->status,
            'priority'    => $task->priority,
            'assigned_to' => $assignee->id
        ]);
        
        $response->assertStatus(200)->assertJsonPath('data.assignee.id', $assignee->id);
    }

    /** @test */
    public function users_can_update_task_status_only_if_authorized_or_admin()
    {
        $task = Task::factory()->create(['project_id' => $this->project->id, 'status' => 'pending']);

        // Admin updates status
        $this->actingAs($this->adminUser, 'sanctum')->patchJson("/api/v1/tasks/{$task->id}/status", [
            'status' => 'in_progress'
        ])->assertStatus(200);
        
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress']);
    }
}
