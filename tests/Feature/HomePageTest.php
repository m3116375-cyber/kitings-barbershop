<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_beranda_dan_login_dapat_dibuka(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_halaman_admin_butuh_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }
}
