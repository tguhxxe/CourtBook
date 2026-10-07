<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Maintenance;
use App\Models\RefundRequest;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use App\Services\Booking\BookingService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthAndAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->travelTo(now()->setDate(2026, 10, 7)->setTime(8, 0));
        $this->customer = User::where('role', 'customer')->first();
        $this->admin = User::where('role', 'admin')->first();
    }

    private function booking(int $hour = 9): Booking
    {
        return app(BookingService::class)->create($this->customer, Court::first(), ['date' => '2026-10-09', 'hour' => $hour, 'duration' => 2]);
    }

    public function test_public_pages_render_and_filters_work(): void
    {
        foreach (['/', '/courts', '/courts/1', '/login', '/register', '/forgot-password'] as $url) {
            $this->get($url)->assertOk();
        }$this->get('/courts?sport=soccer')->assertSee('Mini Soccer Arena')->assertDontSee('Tennis Court A');
        $this->get('/courts?q=missing')->assertSee('Lapangan tidak ditemukan');
        $this->get('/courts?sport=invalid')->assertRedirect();
        $this->get('/courts/1?date=2026-12-01')->assertRedirect();
    }

    public function test_registration_cannot_escalate_role(): void
    {
        $this->post('/register', ['name' => 'Pelanggan baru', 'email' => 'new@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'role' => 'admin'])->assertRedirect('/bookings');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@example.test', 'role' => 'customer']);
    }

    public function test_registration_validation_login_and_logout(): void
    {
        $this->post('/register', ['name' => '', 'email' => 'bad', 'password' => 'short'])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->post('/login', ['email' => $this->customer->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $this->customer->email, 'password' => 'CourtBook123!'])->assertRedirect('/bookings');
        $this->assertAuthenticatedAs($this->customer);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_and_customer_admin_access_denied(): void
    {
        $this->get('/bookings')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs($this->customer)->get('/admin')->assertForbidden();
        $this->post('/admin/courts', [])->assertForbidden();
    }

    public function test_booking_ownership_on_detail_cancel_and_pay(): void
    {
        $b = $this->booking();
        $other = User::factory()->create();
        $this->actingAs($other)->get('/bookings/'.$b->id)->assertForbidden();
        $this->post('/bookings/'.$b->id.'/cancel')->assertForbidden();
        $this->post('/bookings/'.$b->id.'/payments', ['kind' => 'full', 'terms' => '1'])->assertForbidden();
        $this->get('/bookings')->assertDontSee($b->code);
    }

    public function test_password_reset_broker_single_use_and_invalid_token(): void
    {
        Notification::fake();
        $this->post('/forgot-password', ['email' => $this->customer->email])->assertRedirect()->assertSessionHas('status');
        $token = null;
        Notification::assertSentTo($this->customer, ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });
        $this->get('/reset-password/'.$token.'?email='.$this->customer->email)->assertOk();
        $p = ['email' => $this->customer->email, 'token' => $token, 'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'];
        $this->post('/reset-password', $p)->assertRedirect('/login');
        $this->assertTrue(Hash::check('NewPassword123!', $this->customer->fresh()->password));
        $this->post('/reset-password', $p)->assertSessionHasErrors('email');
        $p['token'] = 'invalid';
        $this->post('/reset-password', $p)->assertSessionHasErrors('email');
    }

    public function test_profile_validates_current_password_and_prevents_role_change(): void
    {
        $this->actingAs($this->customer)->put('/profile', ['name' => 'Updated', 'email' => $this->customer->email, 'phone' => '+62 812345678', 'role' => 'admin'])->assertRedirect();
        $this->assertSame('customer', $this->customer->fresh()->role);
        $this->assertSame('Updated', $this->customer->fresh()->name);
        $this->put('/profile', ['name' => 'Updated', 'email' => $this->customer->email, 'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!', 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
    }

    public function test_admin_pages_and_court_crud(): void
    {
        $this->actingAs($this->admin);
        foreach (['/admin', '/admin/courts', '/admin/courts/create', '/admin/courts/1/edit', '/admin/bookings', '/admin/transactions', '/admin/maintenance', '/admin/settings', '/admin/refunds'] as $url) {
            $this->get($url)->assertOk();
        }$d = ['name' => 'Lapangan uji', 'sport' => 'tennis', 'description' => 'Data simulasi', 'facilities' => 'Lampu, Parkir', 'hourly_rate' => 120000, 'active' => 1];
        $this->post('/admin/courts', $d)->assertRedirect('/admin/courts');
        $c = Court::where('name', 'Lapangan uji')->first();
        $d['hourly_rate'] = 150000;
        $this->put('/admin/courts/'.$c->id, $d)->assertRedirect('/admin/courts');
        $this->assertSame(150000, $c->fresh()->hourly_rate);
        $this->delete('/admin/courts/'.$c->id)->assertRedirect();
        $this->assertDatabaseMissing('courts', ['id' => $c->id]);
    }

    public function test_admin_cannot_delete_court_history_or_pay_for_customer(): void
    {
        $b = $this->booking();
        $this->actingAs($this->admin)->delete('/admin/courts/'.$b->court_id)->assertSessionHasErrors('booking');
        $this->post('/bookings/'.$b->id.'/payments', ['kind' => 'full', 'terms' => '1'])->assertForbidden();
        $this->assertSame('unpaid', $b->fresh()->payment_status);
    }

    public function test_maintenance_crud_and_time_validation(): void
    {
        $this->actingAs($this->admin);
        $d = ['court_id' => 1, 'starts_at' => '2026-10-09T09:00', 'ends_at' => '2026-10-09T11:00', 'reason' => 'Demo maintenance'];
        $this->post('/admin/maintenance', $d)->assertRedirect()->assertSessionHasNoErrors();
        $m = Maintenance::firstOrFail();
        $this->assertSame(2, Reservation::count());
        $this->delete('/admin/maintenance/'.$m->id)->assertRedirect();
        $this->assertSame(0, Reservation::count());
        $d['ends_at'] = '2026-10-09T08:00';
        $this->post('/admin/maintenance', $d)->assertSessionHasErrors('ends_at');
    }

    public function test_settings_validate_booking_hours_and_preserve_snapshot(): void
    {
        $b = $this->booking(20);
        $this->actingAs($this->admin)->put('/admin/settings', ['open_hour' => 7, 'close_hour' => 21, 'dp_percent' => 25])->assertSessionHasErrors('booking');
        $this->put('/admin/settings', ['open_hour' => 7, 'close_hour' => 23, 'dp_percent' => 25])->assertSessionHasNoErrors();
        $this->assertSame(25, Setting::find(1)->dp_percent);
        $this->assertSame(50, $b->fresh()->dp_percent);
    }

    public function test_refund_manual_progress_and_transition_validation(): void
    {
        $b = $this->booking();
        $ref = RefundRequest::create(['booking_id' => $b->id, 'reference' => 'test', 'amount' => 50000, 'reason' => 'Demo']);
        $this->actingAs($this->admin)->get('/admin/refunds')->assertOk();
        $this->put('/admin/refunds/'.$ref->id, ['status' => 'processed', 'admin_notes' => 'Premature'])->assertSessionHasErrors('booking');
        $this->put('/admin/refunds/'.$ref->id, ['status' => 'reviewing', 'admin_notes' => 'Meninjau bukti kanal'])->assertSessionHasNoErrors();
        $this->put('/admin/refunds/'.$ref->id, ['status' => 'processed', 'admin_notes' => 'Simulasi penanganan selesai; tidak ada transfer dana'])->assertSessionHasNoErrors();
        $this->assertSame('processed', $ref->fresh()->status);
        $this->assertSame('unpaid', $b->fresh()->payment_status);
    }

    public function test_booking_form_rejects_fractional_input_and_ignores_price(): void
    {
        $this->actingAs($this->customer);
        $d = ['court_id' => 1, 'date' => '2026-10-09', 'hour' => '9:30', 'duration' => 1];
        $this->post('/bookings', $d)->assertSessionHasErrors('hour');
        $d['hour'] = 9;
        $d['duration'] = 1.5;
        $this->post('/bookings', $d)->assertSessionHasErrors('duration');
        $d['duration'] = 1;
        $d['total'] = 1;
        $this->post('/bookings',$d)->assertRedirect();
        $this->assertSame(100000,Booking::first()->total);
    }
}
