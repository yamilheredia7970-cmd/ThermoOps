<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only starts a session for requests it recognizes as coming
        // from a configured SPA origin (see config/sanctum.php `stateful`).
        $this->withHeader('Origin', 'http://localhost:3000');
    }

    public function test_valid_credentials_returns_200_and_authenticates_the_session(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
        $response->assertJsonPath('data.email', $user->email);
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public static function invalidCredentials(): array
    {
        return [
            'wrong_password' => ['correct-password', 'wrong-password'],
            'unknown_email' => [null, 'whatever-password'],
        ];
    }

    #[DataProvider('invalidCredentials')]
    public function test_invalid_credentials_returns_422_and_does_not_authenticate(?string $actualPassword, string $attemptedPassword): void
    {
        $user = User::factory()->create(
            $actualPassword ? ['password' => bcrypt($actualPassword)] : []
        );

        $response = $this->postJson('/api/v1/login', [
            'email' => $actualPassword ? $user->email : 'nobody@thermoops.com',
            'password' => $attemptedPassword,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_missing_credentials_returns_422_with_required_messages(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->create(['password' => bcrypt('correct-password')]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_repeated_failed_attempts_are_throttled(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password')]);

        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertStatus(429);
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/logout');

        $response->assertNoContent();
        // Assert the 'web' guard explicitly: the `auth:sanctum` middleware that
        // just ran calls Auth::shouldUse('sanctum'), which flips the *default*
        // guard for the rest of this test. assertGuest() with no argument would
        // check that now-default 'sanctum' guard, whose RequestGuard caches the
        // user it resolved earlier in the request and would report a false
        // positive here rather than reflecting the logout() call above.
        $this->assertGuest('web');
    }

    public function test_logout_without_authentication_returns_401(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertUnauthorized();
    }

    public function test_me_returns_authenticated_user_with_roles(): void
    {
        Role::findOrCreate('Dispatcher', 'web');
        $user = User::factory()->create();
        $user->assignRole('Dispatcher');

        $response = $this->actingAs($user)->getJson('/api/v1/me');

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
        $response->assertJsonPath('data.roles', ['Dispatcher']);
    }

    public function test_me_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertUnauthorized();
    }
}
