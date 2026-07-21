<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_a_case_insensitive_email_address(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@example.com',
            'password' => Hash::make('kata-sandi-yang-benar'),
        ]);

        $response = $this->post('/login', [
            'email' => '  OPERATOR@EXAMPLE.COM  ',
            'password' => 'kata-sandi-yang-benar',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_an_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'operator@example.com',
            'password' => Hash::make('kata-sandi-yang-benar'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'operator@example.com',
            'password' => 'kata-sandi-yang-salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
