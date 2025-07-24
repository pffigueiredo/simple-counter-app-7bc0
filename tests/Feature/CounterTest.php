<?php

namespace Tests\Feature;

use App\Models\Counter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_counter_page_displays_initial_count(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('welcome')
                ->has('count')
                ->where('count', 0)
        );
    }

    public function test_counter_can_be_incremented(): void
    {
        // Create initial counter
        Counter::create(['count' => 5]);

        $response = $this->post('/counter');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('welcome')
                ->has('count')
                ->where('count', 6)
        );

        $this->assertDatabaseHas('counters', [
            'count' => 6
        ]);
    }

    public function test_counter_persists_across_requests(): void
    {
        // First request creates and increments counter
        $this->post('/counter');
        
        // Second request should show incremented value
        $response = $this->get('/');
        
        $response->assertInertia(fn ($page) => 
            $page->component('welcome')
                ->where('count', 1)
        );
    }

    public function test_counter_creates_new_record_if_none_exists(): void
    {
        $this->assertDatabaseCount('counters', 0);

        $this->get('/');

        $this->assertDatabaseCount('counters', 1);
        $this->assertDatabaseHas('counters', [
            'count' => 0
        ]);
    }
}