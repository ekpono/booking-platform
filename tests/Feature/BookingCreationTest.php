<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_create_a_booking_successfully(): void
    {
        $bookingData = [
            'client_id' => $this->client->id,
            'title' => 'Project Discussion',
            'description' => 'Discuss project requirements and timeline',
            'start_time' => now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'start_time',
                    'end_time',
                    'user' => ['id', 'name', 'email'],
                    'client' => ['id', 'name', 'email'],
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.title', 'Project Discussion')
            ->assertJsonPath('data.client.id', $this->client->id);

        $this->assertDatabaseHas('bookings', [
            'title' => 'Project Discussion',
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
        ]);
    }

    public function test_can_create_a_booking_without_description(): void
    {
        $bookingData = [
            'client_id' => $this->client->id,
            'title' => 'Quick Meeting',
            'start_time' => now()->addDay()->setHour(14)->setMinute(0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->setHour(15)->setMinute(0)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Quick Meeting')
            ->assertJsonPath('data.description', null);

        $this->assertDatabaseHas('bookings', [
            'title' => 'Quick Meeting',
            'description' => null,
        ]);
    }

    public function test_cannot_create_booking_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id', 'title', 'start_time', 'end_time']);
    }

    public function test_cannot_create_booking_with_invalid_client(): void
    {
        $bookingData = [
            'client_id' => 99999,
            'title' => 'Meeting',
            'start_time' => now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_id']);
    }

    public function test_cannot_create_booking_with_end_time_before_start_time(): void
    {
        $bookingData = [
            'client_id' => $this->client->id,
            'title' => 'Invalid Meeting',
            'start_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }

    public function test_booking_is_associated_with_authenticated_user(): void
    {
        $anotherUser = User::factory()->create();

        $bookingData = [
            'client_id' => $this->client->id,
            'title' => 'My Booking',
            'start_time' => now()->addDay()->setHour(9)->setMinute(0)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.id', $this->user->id);

        // Verify it's not associated with another user
        $this->assertDatabaseHas('bookings', [
            'title' => 'My Booking',
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseMissing('bookings', [
            'title' => 'My Booking',
            'user_id' => $anotherUser->id,
        ]);
    }
}
