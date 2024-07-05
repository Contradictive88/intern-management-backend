<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $password;

    /**
     * Set up the test environment.
     *
     * This method is called before each test method runs.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Define a common password
        $this->password = 'password123';

        // Create a user with known credentials
        $this->user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt($this->password),
        ]);
    }

    /**
     * Tear down the test environment.
     *
     * This method is called after each test method runs.
     */
    protected function tearDown(): void
    {
        // Clear user data if needed (database is refreshed by RefreshDatabase)
        $this->user = null;

        parent::tearDown();
    }

    /**
     * Test user login with valid credentials.
     *
     * @return void
     */
    public function test_login_with_valid_credentials()
    {
        // Make a POST request to the login endpoint with valid credentials
        $response = $this->postJson('/api/login', [
            'username' => $this->user->username,
            'password' => $this->password,
        ]);

        // Assert that the response is successful and contains the expected data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'username',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'username' => 'testuser',
                ],
                'message' => 'User logged in successfully.',
            ]);
    }

    /**
     * Test user login with invalid credentials.
     *
     * @return void
     */
    public function test_login_with_invalid_credentials()
    {
        // Make a POST request to the login endpoint with invalid credentials
        $response = $this->postJson('/api/login', [
            'username' => $this->user->username,
            'password' => 'wrongpassword',
        ]);

        // Assert that the response is unauthorized
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => ['error' => 'Invalid credentials']
            ]);
    }

    /**
     * Test user logout.
     *
     * @return void
     */
    public function test_logout()
    {
        // Generate a token for the user
        $token = $this->user->createToken('auth_token')->plainTextToken;

        // Make a POST request to the logout endpoint with the token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        // Assert that the response is successful
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User logged out successfully.',
            ]);
    }
}
