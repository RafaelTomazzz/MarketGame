<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_is_logged_out_and_redirected_home(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_when_attempting_to_log_out(): void
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_header_contains_csrf_protected_logout_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('action="'.route('logout').'"', false);
        $response->assertSee('name="_token"', false);
        $response->assertSeeText('Sair');
    }
}
