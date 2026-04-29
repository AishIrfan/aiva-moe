<?php

namespace Tests\Feature;

use App\Models\AbsentReason;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AbsentReason::create(['code' => 'MC', 'label' => 'Medical', 'is_excused' => true, 'counts_as_present' => false, 'order' => 1]);
    }

    private function actingUser(): User
    {
        $school = School::create(['name' => 'T', 'code' => 'T']);
        return User::create([
            'name' => 'U', 'email' => 'u@e.test', 'password' => bcrypt('x'),
            'role' => User::ROLE_SCHOOL_ADMIN, 'mode' => 'school', 'school_id' => $school->id,
        ]);
    }

    public function test_overview_loads(): void
    {
        $this->actingAs($this->actingUser())->get('/school/overview')->assertOk();
    }

    public function test_alerts_loads(): void
    {
        $this->actingAs($this->actingUser())->get('/school/alerts')->assertOk();
    }

    public function test_attendance_loads_and_seeds(): void
    {
        $this->actingAs($this->actingUser())->get('/school/attendance')->assertOk();
    }

    public function test_moe_role_required(): void
    {
        $this->actingAs($this->actingUser())->get('/moe/overview')->assertForbidden();
    }
}
