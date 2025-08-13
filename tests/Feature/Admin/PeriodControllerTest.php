<?php

namespace Tests\Feature\Admin;

use App\Models\RegistrationPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
class PeriodControllerTest extends TestCase
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
    public function it_stores_a_new_period()
    {
        $data = [
            'code' => '2024-01',
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ];

        $response = $this->post('/admin/periods', $data);
        $response->assertRedirect(route('admin.periods.index'))
                ->assertSessionHas('success');

        $this->assertDatabaseHas('registration_periods', $data);
    }

    /** @test */
    public function it_validates_end_date_after_start_date()
    {
        $data = [
            'code' => '2024-01',
            'start_date' => '2024-06-01',
            'end_date' => '2024-01-01', // Fecha inválida
        ];

        $response = $this->post('/admin/periods', $data);
        $response->assertSessionHasErrors('end_date');
    }

    /** @test */
    public function it_deletes_a_period_with_no_associated_records()
    {
        $period = RegistrationPeriod::factory()->create();

        $response = $this->delete("/admin/periods/{$period->period_id}");
        $response->assertRedirect(route('admin.periods.index'))
                ->assertSessionHas('success');

        $this->assertDatabaseMissing('registration_periods', [
            'period_id' => $period->period_id
        ]);
    }
}