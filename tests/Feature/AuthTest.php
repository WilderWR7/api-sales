<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function userCanRegister(): void
    {
        $userEmail = 'newuser@example.com';
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => $userEmail,
            'password' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'access_token',
                'token_type'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userEmail,
        ]);
    }

    #[Test]
    public function userCanLoginWithCorrectCredentials(): void
    {
        $userEmail = 'testUser@example.com';
        $userPassword = 'secret-pass';
        User::factory()->create([
            'email' => $userEmail,
            'password' => Hash::make($userPassword),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $userEmail,
            'password' => $userPassword,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user',
                'access_token',
                'token_type'
            ]);
    }

    #[Test]
    public function userCannotLoginWithIncorrectPassword(): void
    {
        $userEmail = 'testUser2@example.com';
        $userPassword = 'secret-pass';
        User::factory()->create([
            'email' => $userEmail,
            'password' => Hash::make($userPassword),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $userEmail,
            'password' => 'wrong-pass',
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    #[Test]
    public function authenticatedUserCanAccessProtectedRoute(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertOk()
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }

    #[Test]
    public function userCanLogout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertNoContent();
        $response->assertHeaderMissing('content-type');

        // Force Laravel to forget the authentication in the test container cache
        \Illuminate\Support\Facades\Auth::forgetGuards();

        // Trying to access protected route again should fail
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response2->assertUnauthorized();
    }
}
