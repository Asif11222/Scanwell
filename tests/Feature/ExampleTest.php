<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $admin = \App\Models\Admin::first();
        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertStatus(200);
    }
}
