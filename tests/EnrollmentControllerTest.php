<?php

namespace Tests\Feature\Student;

use App\Models\PlannedSubject;
use App\Models\RegistrationPeriod;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class EnrollmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $currentPeriod;

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
    public function it_displays_available_subjects()
    {
        $subject = Subject::factory()->create();

        $response = $this->get('/student/pre-enrollment');
        $response->assertStatus(200)
                ->assertViewHas('availableSubjects');
    }

    /** @test */
    public function it_stores_planned_subjects()
    {
        $subject = Subject::factory()->create();

        $response = $this->post('/student/pre-enrollment/process', [
            'subjects' => [$subject->id],
            'period_id' => $this->currentPeriod->id
        ]);

        $response->assertRedirect()
                ->assertSessionHas('success');

        $this->assertDatabaseHas('planned_subjects', [
            'student_id' => $this->student->id,
            'subject_id' => $subject->id
        ]);
    }

    /** @test */
    public function it_deletes_planned_subject()
    {
        $plannedSubject = PlannedSubject::factory()->create([
            'student_id' => $this->student->id
        ]);

        $response = $this->delete("/student/pre-enrollment/planned-subject/{$plannedSubject->subject_id}");
        $response->assertRedirect()
                ->assertSessionHas('success');

        $this->assertDatabaseMissing('planned_subjects', [
            'id' => $plannedSubject->id
        ]);
    }
}