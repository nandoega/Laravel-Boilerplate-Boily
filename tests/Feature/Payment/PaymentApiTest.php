<?php

namespace Tests\Feature\Payment;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $normalUser;
    protected $invoice;

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
        $this->invoice = Invoice::factory()->create(['project_id' => $project->id, 'total' => 2000, 'status' => 'sent']);
    }

    /** @test */
    public function authenticaton_required_for_payments()
    {
        $this->getJson('/api/v1/payments')->assertStatus(401);
    }

    /** @test */
    public function only_admins_can_record_payments()
    {
        $this->actingAs($this->normalUser, 'sanctum')
             ->postJson('/api/v1/payments', [
                 'invoice_id' => $this->invoice->id,
                 'amount' => 1000,
                 'payment_date' => '2025-10-10'
             ])
             ->assertStatus(403);
    }

    /** @test */
    public function recording_a_payment_requires_invoice()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/payments', [
            'amount' => 1000,
            'payment_date' => '2025-10-10'
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['invoice_id']);
    }

    /** @test */
    public function admin_can_record_payment_for_invoice()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/payments', [
             'invoice_id' => $this->invoice->id,
             'amount' => 1500,
             'payment_date' => '2025-10-10',
             'payment_method' => 'bank_transfer',
             'reference_number' => 'REF-12345'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.amount', 1500)
                 ->assertJsonPath('data.payment_method', 'bank_transfer');

        $this->assertDatabaseHas('payments', ['invoice_id' => $this->invoice->id, 'amount' => 1500]);
    }
    
    /** @test */
    public function payment_amount_cannot_exceed_certain_types()
    {
        // For standard data boundaries
         $response = $this->actingAs($this->adminUser, 'sanctum')->postJson('/api/v1/payments', [
             'invoice_id' => $this->invoice->id,
             'amount' => -50, // negative not allowed based on typical rules (though custom rules might apply, verifying generic failure)
             'payment_date' => '2025-10-10',
        ]);
        
        // As long as min:0.01 exists in StorePaymentRequest, this should 422
        $response->assertStatus(422)->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function deleting_payment_record()
    {
        $payment = Payment::factory()->create(['invoice_id' => $this->invoice->id, 'amount' => 500]);
        
        $response = $this->actingAs($this->adminUser, 'sanctum')->deleteJson('/api/v1/payments/' . $payment->id);
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
