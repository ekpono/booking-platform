<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingOverlapTest extends TestCase
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

    public function test_cannot_create_booking_that_overlaps_with_existing_booking(): void
    {
        // Create an existing booking from 10:00 to 11:00
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create(['title' => 'Existing Booking']);

        // Try to create a booking from 10:30 to 11:30 (overlaps)
        $overlappingBooking = [
            'client_id' => $this->client->id,
            'title' => 'Overlapping Booking',
            'start_time' => now()->addDay()->setHour(10)->setMinute(30)->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->setHour(11)->setMinute(30)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', $overlappingBooking);

        $response->assertStatus(422)
            ->assertJsonPath('errors.overlap.0', 'The booking overlaps with an existing booking for this user.');

        $this->assertDatabaseMissing('bookings', [
            'title' => 'Overlapping Booking',
        ]);
    }

    public function test_cannot_create_booking_that_starts_during_existing_booking(): void
    {
        // Existing booking: 10:00 - 12:00
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(12)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create();

        // Try to create: 11:00 - 13:00 (starts during existing)
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'client_id' => $this->client->id,
                'title' => 'Starts During',
                'start_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(13)->setMinute(0)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['overlap']);
    }

    public function test_cannot_create_booking_that_ends_during_existing_booking(): void
    {
        // Existing booking: 10:00 - 12:00
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(12)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create();

        // Try to create: 09:00 - 11:00 (ends during existing)
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'client_id' => $this->client->id,
                'title' => 'Ends During',
                'start_time' => now()->addDay()->setHour(9)->setMinute(0)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['overlap']);
    }

    public function test_cannot_create_booking_that_completely_contains_existing_booking(): void
    {
        // Existing booking: 10:00 - 11:00
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create();

        // Try to create: 09:00 - 12:00 (completely contains existing)
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'client_id' => $this->client->id,
                'title' => 'Contains Existing',
                'start_time' => now()->addDay()->setHour(9)->setMinute(0)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(12)->setMinute(0)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['overlap']);
    }

    public function test_cannot_create_booking_completely_within_existing_booking(): void
    {
        // Existing booking: 09:00 - 12:00
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(9)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(12)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create();

        // Try to create: 10:00 - 11:00 (completely within existing)
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'client_id' => $this->client->id,
                'title' => 'Within Existing',
                'start_time' => now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['overlap']);
    }

    public function test_can_create_booking_immediately_after_existing_booking(): void
    {
        // Existing booking: 10:00 - 11:00
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create();

        // Create: 11:00 - 12:00 (starts exactly when previous ends - no overlap)
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'client_id' => $this->client->id,
                'title' => 'After Existing',
                'start_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(12)->setMinute(0)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'title' => 'After Existing',
        ]);
    }

    public function test_can_create_booking_immediately_before_existing_booking(): void
    {
        // Existing booking: 11:00 - 12:00
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(12)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create();

        // Create: 10:00 - 11:00 (ends exactly when next starts - no overlap)
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'client_id' => $this->client->id,
                'title' => 'Before Existing',
                'start_time' => now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'title' => 'Before Existing',
        ]);
    }

    public function test_different_users_can_have_overlapping_bookings(): void
    {
        $otherUser = User::factory()->create();

        // Other user's booking: 10:00 - 11:00
        Booking::factory()
            ->forUser($otherUser)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create();

        // This user creates same time slot - should work (different user)
        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'client_id' => $this->client->id,
                'title' => 'Same Time Different User',
                'start_time' => now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'title' => 'Same Time Different User',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_cannot_update_booking_to_overlap_with_another(): void
    {
        // Existing booking 1: 10:00 - 11:00
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(11)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create(['title' => 'First Booking']);

        // Existing booking 2: 14:00 - 15:00
        $bookingToUpdate = Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(14)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(15)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create(['title' => 'Second Booking']);

        // Try to update booking 2 to overlap with booking 1
        $response = $this->actingAs($this->user)
            ->putJson("/api/bookings/{$bookingToUpdate->id}", [
                'start_time' => now()->addDay()->setHour(10)->setMinute(30)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(11)->setMinute(30)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['overlap']);
    }

    public function test_can_update_booking_within_its_own_time_range(): void
    {
        // Existing booking: 10:00 - 12:00
        $booking = Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->withTimeRange(
                now()->addDay()->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
                now()->addDay()->setHour(12)->setMinute(0)->format('Y-m-d H:i:s')
            )
            ->create(['title' => 'Original']);

        // Update to a different time within same slot - should work
        $response = $this->actingAs($this->user)
            ->putJson("/api/bookings/{$booking->id}", [
                'start_time' => now()->addDay()->setHour(10)->setMinute(30)->format('Y-m-d H:i:s'),
                'end_time' => now()->addDay()->setHour(11)->setMinute(30)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);
    }
}
