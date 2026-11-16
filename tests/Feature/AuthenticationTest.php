<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_authentication_pages_are_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('チームチャットアプリ')
            ->assertSee('ログイン');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('新規登録');
    }

    public function test_guest_can_register_and_is_redirected_to_login(): void
    {
        $response = $this->post(route('register.store'), $this->registrationData());

        $response->assertRedirect(route('login'));
        $this->assertGuest();

        $user = User::where('email', 'hanako@example.com')->firstOrFail();
        $this->assertSame('山田 花子', $user->name);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_authenticated_user_can_register_without_losing_the_current_session(): void
    {
        $currentUser = User::factory()->create();

        $this->actingAs($currentUser)
            ->get(route('register'))
            ->assertOk()
            ->assertSee('新規登録');

        $this->actingAs($currentUser)
            ->post(route('register.store'), $this->registrationData())
            ->assertRedirect(route('login'));

        $this->assertAuthenticatedAs($currentUser);
        $this->get(route('login'))->assertOk()->assertSee('ログイン');
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'hanako@example.com']);

        $this->post(route('register.store'), $this->registrationData())
            ->assertSessionHasErrors([
                'email' => 'このメールアドレスはすでに登録されています',
            ]);
    }

    public function test_registration_rejects_invalid_email_and_short_password(): void
    {
        $data = $this->registrationData([
            'email' => 'invalid-email',
            'password' => 'short7',
            'password_confirmation' => 'short7',
        ]);

        $this->post(route('register.store'), $data)
            ->assertSessionHasErrors([
                'email' => 'メールアドレスの形式が正しくありません',
                'password' => '8文字以上で入力してください',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_email_over_254_characters(): void
    {
        $data = $this->registrationData([
            'email' => str_repeat('a', 243).'@example.com',
        ]);

        $this->post(route('register.store'), $data)
            ->assertSessionHasErrors([
                'email' => 'メールアドレスは254文字以内で入力してください',
            ]);
    }

    public function test_registration_rejects_long_name_and_mismatched_password_confirmation(): void
    {
        $data = $this->registrationData([
            'name' => str_repeat('あ', 31),
            'password_confirmation' => 'different123',
        ]);

        $this->post(route('register.store'), $data)
            ->assertSessionHasErrors([
                'name' => '表示名は30文字以内で入力してください',
                'password' => 'パスワードが一致しません',
            ]);
    }

    public function test_login_redirects_to_channels_and_can_switch_authenticated_user(): void
    {
        $currentUser = User::factory()->create();
        $targetUser = User::factory()->create([
            'email' => 'target@example.com',
            'password' => 'secret123',
        ]);

        $this->actingAs($currentUser)
            ->post(route('login.store'), [
                'email' => $targetUser->email,
                'password' => 'secret123',
            ])
            ->assertRedirect('/channels');

        $this->assertAuthenticatedAs($targetUser);
    }

    public function test_invalid_credentials_show_the_same_japanese_error(): void
    {
        $user = User::factory()->create([
            'email' => 'registered@example.com',
            'password' => 'secret123',
        ]);

        $message = 'メールアドレスまたはパスワードが正しくありません';

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['password' => $message]);

        $this->post(route('login.store'), [
            'email' => 'unknown@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['password' => $message]);

        $this->assertGuest();
    }

    public function test_failed_login_keeps_the_current_authenticated_user(): void
    {
        $currentUser = User::factory()->create();

        $this->actingAs($currentUser)
            ->post(route('login.store'), [
                'email' => 'unknown@example.com',
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors([
                'password' => 'メールアドレスまたはパスワードが正しくありません',
            ]);

        $this->assertAuthenticatedAs($currentUser);
    }

    public function test_logout_ends_the_session_and_redirects_to_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => '山田 花子',
            'email' => 'hanako@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], $overrides);
    }
}
