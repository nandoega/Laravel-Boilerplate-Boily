<?php

namespace Tests\Feature\Report;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportApiTest extends TestCase
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
    public function standard_users_cannot_view_dashboard_reports()
    {
        $this->actingAs($this->normalUser, 'sanctum')->getJson('/api/v1/reports/dashboard')->assertStatus(403);
        $this->actingAs($this->normalUser, 'sanctum')->getJson('/api/v1/reports/project-profitability')->assertStatus(403);
    }
    
    /** @test */
    public function admin_can_view_dashboard_summary()
    {
        $client = Client::factory()->create();
        Project::factory()->create(['client_id' => $client->id, 'status' => 'in_progress']);
        Invoice::factory()->create(['project_id' => Project::first()->id, 'status' => 'paid', 'total' => 5000]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->getJson('/api/v1/reports/dashboard');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'active_projects_count',
                         'pending_invoices_count',
                         'total_collected',
                         'active_team_members',
                         'total_invoiced',
                     ],
                     'meta'
                 ]);
                 
        $this->assertEquals(1, $response->json('data.active_projects_count'));
        $this->assertEquals(5000, $response->json('data.total_collected'));
    }

    /** @test */
    public function admin_can_view_project_profitability_report()
    {
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        
        Invoice::factory()->create(['project_id' => $project->id, 'status' => 'paid', 'total' => 2000]);
        TimeEntry::factory()->create(['task_id' => $task->id, 'hours' => 10, 'hourly_rate' => 50]); // 500 cost

        $response = $this->actingAs($this->adminUser, 'sanctum')->getJson('/api/v1/reports/project-profitability');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals(2000, $data[0]['total_invoiced']);
        $this->assertEquals(500, $data[0]['total_cost']);
        $this->assertEquals(1500, $data[0]['profit']);
        $this->assertEquals(75, $data[0]['profit_margin']);
    }
    
     /** @test */
    public function productivity_report_calculates_billable_hours_correctly()
    {
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        
        TimeEntry::factory()->create([
            'task_id' => $task->id, 
            'user_id' => $this->normalUser->id,
            'hours' => 5, 
            'hourly_rate' => 100,
            'is_billable' => true
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')->getJson('/api/v1/reports/team-productivity');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(5, $data[0]['total_hours']);
        $this->assertEquals(500, $data[0]['total_billable_amount']);
    }
}
