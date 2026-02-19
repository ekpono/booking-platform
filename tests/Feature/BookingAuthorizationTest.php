<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Client $client;
    private Client $otherClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        $this->otherClient = Client::factory()->create(['user_id' => $this->otherUser->id]);
    }

    public function test_users_can_only_view_their_own_booking(): void
    {
        $booking = Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create(['title' => 'My Booking']);

        $otherBooking = Booking::factory()
            ->forUser($this->otherUser)
            ->forClient($this->otherClient)
            ->create(['title' => 'Other Booking']);

        // Can view own booking
        $response = $this->actingAs($this->user)
            ->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'My Booking');

        // Cannot view other user's booking
        $response = $this->actingAs($this->user)
            ->getJson("/api/bookings/{$otherBooking->id}");

        $response->assertStatus(403);
    }

    public function test_users_can_only_update_their_own_booking(): void
    {
        $booking = Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'My Booking',
                'start_time' => now()->addDay()->setHour(10)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(11)->format('Y-m-d H:i:s'),
            ]);

        $otherBooking = Booking::factory()
            ->forUser($this->otherUser)
            ->forClient($this->otherClient)
            ->create([
                'title' => 'Other Booking',
                'start_time' => now()->addDay()->setHour(10)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(11)->format('Y-m-d H:i:s'),
            ]);

        $updateData = [
            'client_id' => $this->client->id,
            'title' => 'Updated Title',
            'start_time' => now()->addDay()->setHour(12)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->setHour(13)->format('Y-m-d H:i:s'),
        ];

        // Can update own booking
        $response = $this->actingAs($this->user)
            ->putJson("/api/bookings/{$booking->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        // Cannot update other user's booking
        $response = $this->actingAs($this->user)
            ->putJson("/api/bookings/{$otherBooking->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_users_can_only_delete_their_own_booking(): void
    {
        $booking = Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create(['title' => 'My Booking']);

        $otherBooking = Booking::factory()
            ->forUser($this->otherUser)
            ->forClient($this->otherClient)
            ->create(['title' => 'Other Booking']);

        // Can delete own booking
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/bookings/{$booking->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);

        // Cannot delete other user's booking
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/bookings/{$otherBooking->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('bookings', ['id' => $otherBooking->id]);
    }
}
