<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_users_can_only_see_their_own_clients(): void
    {
        // Create clients for this user
        Client::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My Client 1',
            'email' => 'client1@example.com',
        ]);

        Client::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My Client 2',
            'email' => 'client2@example.com',
        ]);

        // Create client for other user
        Client::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => 'Other Client',
            'email' => 'other@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertContains('My Client 1', $names);
        $this->assertContains('My Client 2', $names);
        $this->assertNotContains('Other Client', $names);
    }

    public function test_clients_are_associated_with_authenticated_user_on_creation(): void
    {
        $clientData = [
            'name' => 'New Client',
            'email' => 'newclient@example.com',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/clients', $clientData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('clients', [
            'name' => 'New Client',
            'email' => 'newclient@example.com',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_users_can_only_view_their_own_client(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My Client',
            'email' => 'myclient@example.com',
        ]);

        $otherClient = Client::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => 'Other Client',
            'email' => 'otherclient@example.com',
        ]);

        // Can view own client
        $response = $this->actingAs($this->user)
            ->getJson("/api/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'My Client');

        // Cannot view other user's client
        $response = $this->actingAs($this->user)
            ->getJson("/api/clients/{$otherClient->id}");

        $response->assertStatus(403);
    }

    public function test_users_can_only_delete_their_own_client(): void
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My Client',
            'email' => 'myclient@example.com',
        ]);

        $otherClient = Client::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => 'Other Client',
            'email' => 'otherclient@example.com',
        ]);

        // Can delete own client
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);

        // Cannot delete other user's client
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/clients/{$otherClient->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('clients', ['id' => $otherClient->id]);
    }

    public function test_different_users_can_have_clients_with_same_email(): void
    {
        $clientData = [
            'name' => 'Test Client',
            'email' => 'same@example.com',
        ];

        // First user creates client
        $response = $this->actingAs($this->user)
            ->postJson('/api/clients', $clientData);

        $response->assertStatus(201);

        // Second user can also create client with same email
        $response = $this->actingAs($this->otherUser)
            ->postJson('/api/clients', $clientData);

        $response->assertStatus(201);

        // Both clients should exist in database
        $this->assertDatabaseCount('clients', 2);
    }
}
