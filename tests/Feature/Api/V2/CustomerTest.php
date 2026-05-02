<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V2;

use App\Models\MasterCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token', ['read:customers']);
    }

    public function test_customer_list_requires_authentication(): void
    {
        $response = $this->getJson('/api/v2/customers');
        $response->assertStatus(401);
    }

    public function test_customer_list_returns_paginated_data(): void
    {
        MasterCustomer::factory()->count(3)->create();

        Sanctum::actingAs($this->user, ['read:customers']);

        $response = $this->getJson('/api/v2/customers');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_customer_list_supports_search(): void
    {
        MasterCustomer::factory()->create(['name' => 'John Doe']);
        MasterCustomer::factory()->create(['name' => 'Jane Smith']);

        Sanctum::actingAs($this->user, ['read:customers']);

        $response = $this->getJson('/api/v2/customers?search=John');
        $response->assertStatus(200);
        $this->assertStringContainsString('John Doe', $response->getContent());
    }

    public function test_customer_show_returns_single_customer(): void
    {
        $customer = MasterCustomer::factory()->create();

        Sanctum::actingAs($this->user, ['read:customers']);

        $response = $this->getJson("/api/v2/customers/{$customer->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $customer->id);
    }

    public function test_customer_show_returns_404_for_missing(): void
    {
        Sanctum::actingAs($this->user, ['read:customers']);

        $response = $this->getJson('/api/v2/customers/99999');
        $response->assertStatus(404);
    }
}
