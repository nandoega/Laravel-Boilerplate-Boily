<?php

namespace Tests\Feature\Invoice;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
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
    public function unauthenticated_access_is_rejected()
    {
        $this->getJson('/api/v1/invoices')->assertStatus(401);
        $this->postJson('/api/v1/invoices', [])->assertStatus(401);
    }

    /** @test */
    public function standard_users_cannot_create_or_delete_invoices()
    {
        $this->actingAs($this->normalUser, 'sanctum')->postJson('/api/v1/invoices', [
            'project_id' => $this->project->id,
            'issue_date' => '2025-10-10'
        ])->assertStatus(403);
        
        $invoice = Invoice::factory()->create(['project_id' => $this->project->id]);
        $this->actingAs($this->normalUser, 'sanctum')->deleteJson('/api/v1/invoices/' . $invoice->id)->assertStatus(403);
    }

    /** @test */
    public function invoice_validation_requires_critical_fields()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/invoices', [
            'tax_rate' => 10
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id', 'issue_date', 'due_date', 'subtotal']);
    }

    /** @test */
    public function admin_can_create_invoice_successfully()
    {
        $data = [
            'project_id' => $this->project->id,
            'issue_date' => '2025-10-01',
            'due_date' => '2025-10-15',
            'subtotal' => 1000,
            'tax_rate' => 10,
            'total' => 1100,
            'notes' => 'Design wireframes phase 1'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/invoices', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.subtotal', 1000)
                 ->assertJsonPath('data.total', 1100)
                 ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('invoices', ['total' => 1100]);
    }

    /** @test */
    public function updating_invoice_status_requires_valid_enum()
    {
        $invoice = Invoice::factory()->create(['project_id' => $this->project->id, 'status' => 'draft']);

        $response = $this->actingAs($this->adminUser, 'sanctum')->putJson('/api/v1/invoices/' . $invoice->id, [
            'project_id' => $this->project->id,
            'issue_date' => '2025-10-01',
            'due_date' => '2025-10-15',
            'subtotal' => 1000,
            'tax_rate' => 10,
            'total' => 1100,
            'status' => 'invalid_status' // wrong status
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function admin_can_update_invoice_status_to_sent()
    {
        $invoice = Invoice::factory()->create(['project_id' => $this->project->id, 'status' => 'draft']);

        $response = $this->actingAs($this->adminUser, 'sanctum')->putJson('/api/v1/invoices/' . $invoice->id, [
             'project_id' => $this->project->id,
             'issue_date' => $invoice->issue_date->format('Y-m-d'),
             'due_date' => '2025-10-15',
             'subtotal' => 1000,
             'tax_rate' => 10,
             'total' => 1100,
             'status' => 'sent'
        ]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'sent');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'sent']);
    }
}
