<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_simulation_and_unapproved_theme(): void
    {
        $this->seed();
        $this->get('/')->assertOk()->assertSee('DATA SIMULASI')->assertSee('Tema masih membutuhkan persetujuan asisten.');
    }
}
