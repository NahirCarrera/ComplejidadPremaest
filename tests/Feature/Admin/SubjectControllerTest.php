<?php

namespace Tests\Feature\Admin;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
class SubjectControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\User
     */
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        
        // Crear usuario admin y rol
        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        Role::create(['name' => 'admin']);
        $this->admin->assignRole('admin');

        $this->actingAs($this->admin);
    }

    /** @test */
    public function it_filters_subjects_by_level()
    {
        Subject::factory()->create(['level' => 1]);
        Subject::factory()->create(['level' => 2]);

        $response = $this->get('/admin/subjects/demand?level=1');
        $response->assertOk()
                ->assertViewHas('subjects', function ($subjects) {
                    return $subjects->every(fn ($subject) => $subject->level == 1);
                });
    }

    /** @test */
    public function it_shows_zero_demand_subjects()
    {
        $subject = Subject::factory()->create();

        $response = $this->get('/admin/subjects/demand');
        $response->assertViewHas('zeroDemandCount', 1); // 1 asignatura sin demanda
    }
}