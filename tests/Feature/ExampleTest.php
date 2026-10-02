<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_hse_form_saves_payload_to_database(): void
    {
        $response = $this->post('/hse-form', [
            'tanggal' => '2026-09-23',
            'kwh' => '123.45',
            'consumed' => '20',
            'jam_pencatatan' => '08:30',
            'picture' => 'https://example.com/foto.jpg',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('hse_records', [
            'kwh' => '123.45',
            'consumed' => '20',
            'jam_pencatatan' => '08:30',
        ]);
    }
}
