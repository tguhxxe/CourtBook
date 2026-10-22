<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_simulation_and_unapproved_theme(): void
    {
        $this->seed();
        $this->get('/')->assertOk()->assertSee('Data simulasi')->assertSee('Tema masih membutuhkan persetujuan asisten.');
    }

    public function test_home_uses_current_settings_and_explains_when_no_courts_are_active(): void
    {
        $this->seed();
        Setting::findOrFail(1)->update(['open_hour' => 9, 'close_hour' => 21, 'dp_percent' => 35]);
        Court::query()->update(['active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('09.00–21.00 WIB')
            ->assertSee('DP 35%')
            ->assertSee('Lapangan sedang disiapkan.')
            ->assertDontSee('Tennis Court A');
    }
}
