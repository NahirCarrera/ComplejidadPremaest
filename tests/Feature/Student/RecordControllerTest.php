<?php

namespace Tests\Feature\Student;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class RecordControllerTest extends TestCase
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
    public function it_shows_upload_record_form()
    {
        $response = $this->get('/student/records/upload');
        $response->assertStatus(200)
                ->assertViewIs('student.records.upload');
    }

    /** @test */
    public function it_processes_academic_record()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('record.pdf', 1024);

        $response = $this->post('/student/records/process', [
            'academic_record' => $file
        ]);

        $response->assertRedirect('/student/records/approved')
                ->assertSessionHas('success');

        $this->assertTrue(
            Storage::disk('public')->exists("student_records/{$this->student->id}/{$file->hashName()}")
        );
    }

    /** @test */
    public function it_rejects_invalid_file_types()
    {
        $file = UploadedFile::fake()->create('record.txt', 1024);

        $response = $this->post('/student/records/process', [
            'academic_record' => $file
        ]);

        $response->assertSessionHasErrors('academic_record');
    }
}