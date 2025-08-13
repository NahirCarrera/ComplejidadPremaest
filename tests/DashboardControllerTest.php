<?php

namespace Tests\Feature\Admin;

use App\Models\RegistrationPeriod;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class DashboardControllerTest extends TestCase
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
    public function it_loads_the_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200)
                ->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function it_shows_correct_students_count()
    {
        // Crear 5 estudiantes
        Role::create(['name' => 'student']);
        User::factory()->count(5)->create()->each->assignRole('student');

        $response = $this->get('/admin/dashboard');
        $response->assertViewHas('studentsCount', 5);
    }

    /** @test */
    public function it_displays_active_period_data()
    {
        $period = RegistrationPeriod::factory()->create([
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
        ]);

        $response = $this->get('/admin/dashboard');
        $response->assertViewHas('currentPeriod', $period);
    }
}