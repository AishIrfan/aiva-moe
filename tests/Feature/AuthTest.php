<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_authenticated_user_to_overview(): void
    {
        $school = School::create(['name' => 'Test', 'code' => 'T1']);
        $user = User::create([
            'name' => 'U', 'email' => 'u@e.test', 'password' => bcrypt('secret'),
            'role' => User::ROLE_SCHOOL_ADMIN, 'mode' => 'school', 'school_id' => $school->id,
        ]);

        $this->post('/login', ['email' => 'u@e.test', 'password' => 'secret'])
            ->assertRedirect();

        $this->actingAs($user)->get('/')->assertRedirect(route('school.overview'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/school/overview')->assertRedirect('/login');
    }
}
