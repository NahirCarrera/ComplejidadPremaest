<?php
namespace Tests\Feature\Student;

use App\Models\ApprovedSubject;
use App\Models\RegistrationPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\Subject;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        
        // Crear usuario admin y rol
        $this->student = User::factory()->create([
            'name' => 'Student Test',
            'email' => 'student@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        Role::create(['name' => 'student']);
        $this->student->assignRole('student');

        $this->actingAs($this->student);
    }

    /** @test */
    public function it_displays_student_dashboard()
    {
        $response = $this->get('/student/dashboard');
        $response->assertStatus(200)
                ->assertViewIs('student.dashboard');
    }

    /** @test */
    public function it_shows_approved_subjects_count()
    {
        ApprovedSubject::factory()->count(3)->create([
            'student_id' => $this->student->id
        ]);

        $response = $this->get('/student/dashboard');
        $response->assertViewHas('approvedCount', 3);
    }

    /** @test */
    public function it_calculates_pending_credits()
    {
        // Crear asignatura aprobada (3 créditos)
        ApprovedSubject::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => Subject::factory()->create(['credits' => 3])->id
        ]);

        // Crear asignatura existente (5 créditos)
        Subject::factory()->create(['credits' => 5]);

        $response = $this->get('/student/dashboard');
        $response->assertViewHas('pendingCredits', 2); // 5 total - 3 aprobados = 2 pendientes
    }
}