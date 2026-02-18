<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyBookingsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create();
    }

    public function test_can_retrieve_bookings_for_a_specific_week(): void
    {
        // Set a fixed date: Monday, August 4th, 2025
        $mondayOfWeek = Carbon::parse('2025-08-04');

        // Create booking within the target week (Wednesday)
        $withinWeekBooking = Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Within Week Booking',
                'start_time' => $mondayOfWeek->copy()->addDays(2)->setHour(10)->setMinute(0),
                'end_time' => $mondayOfWeek->copy()->addDays(2)->setHour(11)->setMinute(0),
            ]);

        // Create booking outside the target week (previous week)
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Previous Week Booking',
                'start_time' => $mondayOfWeek->copy()->subWeek()->setHour(10)->setMinute(0),
                'end_time' => $mondayOfWeek->copy()->subWeek()->setHour(11)->setMinute(0),
            ]);

        // Create booking outside the target week (next week)
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Next Week Booking',
                'start_time' => $mondayOfWeek->copy()->addWeek()->setHour(10)->setMinute(0),
                'end_time' => $mondayOfWeek->copy()->addWeek()->setHour(11)->setMinute(0),
            ]);

        // Query using a date within the week (Tuesday, August 5th)
        $response = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-05');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Within Week Booking');
    }

    public function test_weekly_api_uses_monday_to_sunday_boundaries(): void
    {
        // Monday, August 4th, 2025
        $monday = Carbon::parse('2025-08-04');

        // Create booking on Monday of the week
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Monday Booking',
                'start_time' => $monday->copy()->setHour(9)->setMinute(0),
                'end_time' => $monday->copy()->setHour(10)->setMinute(0),
            ]);

        // Create booking on Sunday of the week
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Sunday Booking',
                'start_time' => $monday->copy()->addDays(6)->setHour(14)->setMinute(0),
                'end_time' => $monday->copy()->addDays(6)->setHour(15)->setMinute(0),
            ]);

        // Query using Wednesday
        $response = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-06');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertContains('Monday Booking', $titles);
        $this->assertContains('Sunday Booking', $titles);
    }

    public function test_weekly_api_returns_bookings_ordered_by_start_time(): void
    {
        $monday = Carbon::parse('2025-08-04');

        // Create bookings out of order
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Afternoon Booking',
                'start_time' => $monday->copy()->setHour(14)->setMinute(0),
                'end_time' => $monday->copy()->setHour(15)->setMinute(0),
            ]);

        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Morning Booking',
                'start_time' => $monday->copy()->setHour(9)->setMinute(0),
                'end_time' => $monday->copy()->setHour(10)->setMinute(0),
            ]);

        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Late Booking',
                'start_time' => $monday->copy()->addDays(2)->setHour(16)->setMinute(0),
                'end_time' => $monday->copy()->addDays(2)->setHour(17)->setMinute(0),
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-04');

        $response->assertStatus(200);

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertEquals(['Morning Booking', 'Afternoon Booking', 'Late Booking'], $titles);
    }

    public function test_weekly_api_returns_empty_array_when_no_bookings_exist(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-05');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJson(['data' => []]);
    }

    public function test_weekly_api_returns_bookings_from_all_users(): void
    {
        $monday = Carbon::parse('2025-08-04');
        $otherUser = User::factory()->create();

        // This user's booking
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'My Booking',
                'start_time' => $monday->copy()->setHour(10)->setMinute(0),
                'end_time' => $monday->copy()->setHour(11)->setMinute(0),
            ]);

        // Other user's booking
        Booking::factory()
            ->forUser($otherUser)
            ->forClient($this->client)
            ->create([
                'title' => 'Other User Booking',
                'start_time' => $monday->copy()->setHour(14)->setMinute(0),
                'end_time' => $monday->copy()->setHour(15)->setMinute(0),
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-04');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertContains('My Booking', $titles);
        $this->assertContains('Other User Booking', $titles);
    }

    public function test_weekly_api_includes_user_and_client_data(): void
    {
        $monday = Carbon::parse('2025-08-04');

        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Test Booking',
                'start_time' => $monday->copy()->setHour(10)->setMinute(0),
                'end_time' => $monday->copy()->setHour(11)->setMinute(0),
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-04');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
            ]);
    }

    public function test_querying_with_any_day_of_week_returns_same_results(): void
    {
        // Monday, August 4th, 2025
        $monday = Carbon::parse('2025-08-04');

        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Week Booking',
                'start_time' => $monday->copy()->addDays(3)->setHour(10)->setMinute(0),
                'end_time' => $monday->copy()->addDays(3)->setHour(11)->setMinute(0),
            ]);

        // Query with Monday
        $mondayResponse = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-04');

        // Query with Wednesday
        $wednesdayResponse = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-06');

        // Query with Sunday
        $sundayResponse = $this->actingAs($this->user)
            ->getJson('/api/bookings?week=2025-08-10');

        $mondayResponse->assertJsonCount(1, 'data');
        $wednesdayResponse->assertJsonCount(1, 'data');
        $sundayResponse->assertJsonCount(1, 'data');

        $this->assertEquals(
            $mondayResponse->json('data.0.id'),
            $wednesdayResponse->json('data.0.id')
        );

        $this->assertEquals(
            $mondayResponse->json('data.0.id'),
            $sundayResponse->json('data.0.id')
        );
    }

    public function test_without_week_parameter_returns_all_bookings(): void
    {
        $monday = Carbon::parse('2025-08-04');

        // Create bookings in different weeks
        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'This Week',
                'start_time' => $monday->copy()->setHour(10)->setMinute(0),
                'end_time' => $monday->copy()->setHour(11)->setMinute(0),
            ]);

        Booking::factory()
            ->forUser($this->user)
            ->forClient($this->client)
            ->create([
                'title' => 'Next Week',
                'start_time' => $monday->copy()->addWeek()->setHour(10)->setMinute(0),
                'end_time' => $monday->copy()->addWeek()->setHour(11)->setMinute(0),
            ]);

        // Without week parameter
        $response = $this->actingAs($this->user)
            ->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
