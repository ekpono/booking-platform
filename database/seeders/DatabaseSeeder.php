<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo user
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create some clients
        $clients = [
            Client::factory()->create(['name' => 'Acme Corporation', 'email' => 'contact@acme.com']),
            Client::factory()->create(['name' => 'Tech Startup Inc', 'email' => 'hello@techstartup.io']),
            Client::factory()->create(['name' => 'Global Consulting', 'email' => 'info@globalconsulting.com']),
            Client::factory()->create(['name' => 'Creative Agency', 'email' => 'team@creativeagency.co']),
            Client::factory()->create(['name' => 'Local Business LLC', 'email' => 'owner@localbusiness.net']),
        ];

        // Get the current week's Monday
        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);

        // Create bookings for the current week
        $bookings = [
            [
                'client' => $clients[0],
                'title' => 'Project Kickoff Meeting',
                'description' => 'Initial meeting to discuss project scope, timeline, and deliverables.',
                'day_offset' => 0, // Monday
                'start_hour' => 9,
                'duration' => 2,
            ],
            [
                'client' => $clients[1],
                'title' => 'Technical Review',
                'description' => 'Review technical architecture and implementation approach.',
                'day_offset' => 0, // Monday
                'start_hour' => 14,
                'duration' => 1,
            ],
            [
                'client' => $clients[2],
                'title' => 'Strategy Session',
                'description' => 'Quarterly strategy planning and goal setting.',
                'day_offset' => 1, // Tuesday
                'start_hour' => 10,
                'duration' => 2,
            ],
            [
                'client' => $clients[3],
                'title' => 'Design Review',
                'description' => 'Review latest design mockups and provide feedback.',
                'day_offset' => 2, // Wednesday
                'start_hour' => 11,
                'duration' => 1,
            ],
            [
                'client' => $clients[0],
                'title' => 'Progress Update',
                'description' => 'Weekly progress update and status report.',
                'day_offset' => 3, // Thursday
                'start_hour' => 15,
                'duration' => 1,
            ],
            [
                'client' => $clients[4],
                'title' => 'Consultation Call',
                'description' => 'Initial consultation to understand business needs.',
                'day_offset' => 4, // Friday
                'start_hour' => 10,
                'duration' => 1,
            ],
            [
                'client' => $clients[1],
                'title' => 'Sprint Planning',
                'description' => 'Plan next sprint tasks and priorities.',
                'day_offset' => 4, // Friday
                'start_hour' => 14,
                'duration' => 2,
            ],
        ];

        foreach ($bookings as $bookingData) {
            $startTime = $monday->copy()
                ->addDays($bookingData['day_offset'])
                ->setHour($bookingData['start_hour'])
                ->setMinute(0)
                ->setSecond(0);

            $endTime = $startTime->copy()->addHours($bookingData['duration']);

            Booking::factory()->create([
                'user_id' => $user->id,
                'client_id' => $bookingData['client']->id,
                'title' => $bookingData['title'],
                'description' => $bookingData['description'],
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        }

        // Create some bookings for next week too
        $nextMonday = $monday->copy()->addWeek();
        
        Booking::factory()->create([
            'user_id' => $user->id,
            'client_id' => $clients[2]->id,
            'title' => 'Monthly Review',
            'description' => 'Monthly performance review and planning.',
            'start_time' => $nextMonday->copy()->setHour(10)->setMinute(0),
            'end_time' => $nextMonday->copy()->setHour(12)->setMinute(0),
        ]);

        Booking::factory()->create([
            'user_id' => $user->id,
            'client_id' => $clients[3]->id,
            'title' => 'Creative Workshop',
            'description' => 'Brainstorming session for new campaign ideas.',
            'start_time' => $nextMonday->copy()->addDays(2)->setHour(14)->setMinute(0),
            'end_time' => $nextMonday->copy()->addDays(2)->setHour(16)->setMinute(0),
        ]);
    }
}
