<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HostingSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_admin_requires_valid_credentials(): void
    {
        config(['hosting.admin_email' => null, 'hosting.admin_password' => null]);
        $this->artisan('courtbook:bootstrap-admin')->assertFailed();
        $this->assertSame(0, User::count());
        config(['hosting.admin_email' => 'admin@example.com', 'hosting.admin_password' => 'short']);
        $this->artisan('courtbook:bootstrap-admin')->assertFailed();
        $this->assertSame(0, User::count());
    }

    public function test_bootstrap_creates_admin_once_and_does_not_reset_password(): void
    {
        config(['hosting.admin_email' => 'admin@example.com', 'hosting.admin_password' => 'StrongPassword123!']);
        $this->artisan('courtbook:bootstrap-admin')->assertSuccessful();
        $admin = User::firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('StrongPassword123!', $admin->password));
        config(['hosting.admin_password' => null]);
        $this->artisan('courtbook:bootstrap-admin')->assertSuccessful();
        $this->assertSame(1, User::count());
        $this->assertSame($admin->password, $admin->fresh()->password);
    }

    public function test_bootstrap_does_not_promote_existing_customer(): void
    {
        $customer = User::factory()->create(['email' => 'customer@example.com', 'role' => 'customer']);
        config(['hosting.admin_email' => $customer->email, 'hosting.admin_password' => 'StrongPassword123!']);
        $this->artisan('courtbook:bootstrap-admin')->assertFailed();
        $this->assertSame('customer', $customer->fresh()->role);
        $this->assertSame(1, User::count());
    }

    public function test_production_seed_does_not_create_public_demo_accounts(): void
    {
        $this->app->instance('env', 'production');
        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseMissing('users', ['email' => 'admin@courtbook.test']);
        $this->assertDatabaseMissing('users', ['email' => 'pelanggan@courtbook.test']);
        $this->assertDatabaseHas('settings', ['id' => 1]);
        $this->assertDatabaseCount('courts', 3);
    }

    public function test_trusted_hosting_proxy_generates_https_form_urls(): void
    {
        config(['trustedproxy.proxies' => '*']);
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('/login')->assertOk()->assertSee('action="'.str_replace('http://', 'https://', config('app.url')).'/login"', false);
    }
}
