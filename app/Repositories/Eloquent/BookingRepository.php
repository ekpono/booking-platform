<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all bookings for a specific user (paginated).
     */
    public function getByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->with(['client', 'user'])
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    /**
     * Get all bookings for a specific client (paginated).
     */
    public function getByClientId(int $clientId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('client_id', $clientId)
            ->with(['client', 'user'])
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    /**
     * Get bookings for a user within a specific week (Monday-Sunday, paginated).
     */
    public function getByUserForWeek(int $userId, string $dateInWeek, int $perPage = 15): LengthAwarePaginator
    {
        [$weekStart, $weekEnd] = $this->getWeekBoundaries($dateInWeek);

        return $this->model
            ->where('user_id', $userId)
            ->where('start_time', '>=', $weekStart)
            ->where('start_time', '<', $weekEnd)
            ->with(['client', 'user'])
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    /**
     * Get all bookings within a specific week (Monday-Sunday, paginated).
     */
    public function getForWeek(string $dateInWeek, int $perPage = 15): LengthAwarePaginator
    {
        [$weekStart, $weekEnd] = $this->getWeekBoundaries($dateInWeek);

        return $this->model
            ->where('start_time', '>=', $weekStart)
            ->where('start_time', '<', $weekEnd)
            ->with(['client', 'user'])
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    /**
     * Check if a user has overlapping bookings for the given time range.
     */
    public function hasOverlap(int $userId, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        $query = $this->model
            ->where('user_id', $userId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeBookingId !== null) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }

    /**
     * Create a booking with overlap validation.
     *
     * @throws \App\Exceptions\BookingOverlapException
     */
    public function createWithOverlapCheck(array $data): Booking
    {
        if ($this->hasOverlap($data['user_id'], $data['start_time'], $data['end_time'])) {
            throw new \App\Exceptions\BookingOverlapException(
                'The booking overlaps with an existing booking for this user.'
            );
        }

        return $this->create($data);
    }

    /**
     * Update a booking with overlap validation.
     *
     * @throws \App\Exceptions\BookingOverlapException
     */
    public function updateWithOverlapCheck(int $id, array $data): Booking
    {
        $booking = $this->findOrFail($id);

        $userId = $data['user_id'] ?? $booking->user_id;
        $startTime = $data['start_time'] ?? $booking->start_time;
        $endTime = $data['end_time'] ?? $booking->end_time;

        if ($this->hasOverlap($userId, $startTime, $endTime, $id)) {
            throw new \App\Exceptions\BookingOverlapException(
                'The booking overlaps with an existing booking for this user.'
            );
        }

        return $this->update($id, $data);
    }

    /**
     * Get the start and end of the week for a given date.
     *
     * @return array{0: Carbon, 1: Carbon} [weekStart, weekEnd]
     */
    private function getWeekBoundaries(string $dateInWeek): array
    {
        $date = Carbon::parse($dateInWeek);

        // Get Monday of the week (start of week)
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);

        // Get the following Monday (end boundary, exclusive)
        $weekEnd = $weekStart->copy()->addWeek();

        return [$weekStart, $weekEnd];
    }
}
