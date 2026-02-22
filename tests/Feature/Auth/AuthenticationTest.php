<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure roles exist in the testing memory DB before tests
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'sanctum']);
    }

    /**
     * @test
     * @group auth
     */
    public function field_validation_rules_on_registration()
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'phone']);
    }

    /**
     * @test
     * @group auth
     */
    public function cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '123456789'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * @test
     * @group auth
     */
    public function cannot_register_with_short_password()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
            'phone' => '123456789'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @test
     * @group auth
     */
    public function cannot_register_if_password_confirmation_does_not_match()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'mismatch',
            'phone' => '123456789'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @test
     * @group auth
     */
    public function can_register_successfully_with_valid_data()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Valid User',
            'email' => 'valid@example.com',
            'password' => 'securePassword123!',
            'password_confirmation' => 'securePassword123!',
            'phone' => '081234567890'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'roles'],
                    'token'
                ],
                'meta' => ['message']
            ]);
            
        $this->assertDatabaseHas('users', ['email' => 'valid@example.com']);
        
        // Assert base role assigned
        $user = User::where('email', 'valid@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
    }

    /**
     * @test
     * @group auth
     */
    public function field_validation_rules_on_login()
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * @test
     * @group auth
     */
    public function cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['message' => 'Invalid credentials']);
    }

    /**
     * @test
     * @group auth
     */
    public function cannot_login_if_account_is_inactive()
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'is_active' => false
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Account is inactive']);
    }

    /**
     * @test
     * @group auth
     */
    public function can_login_successfully_with_active_account()
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true
        ]);
        $user->assignRole('super-admin');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'active@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['user', 'token'],
                'meta' => ['message']
            ]);
    }

    /**
     * @test
     * @group auth
     */
    public function unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/v1/auth/profile');
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group auth
     */
    public function authenticated_user_can_access_their_profile()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/profile');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.roles.0', 'user');
    }

    /**
     * @test
     * @group auth
     */
    public function unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/v1/auth/logout');
        $response->assertStatus(401);
    }

    /**
     * @test
     * @group auth
     */
    public function authenticated_user_can_logout_and_invalidates_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logged out successfully']);

        // Assert token is deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }
}
