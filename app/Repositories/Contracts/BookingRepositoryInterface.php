<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all bookings for a specific user.
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get all bookings for a specific client.
     */
    public function getByClientId(int $clientId): Collection;

    /**
     * Get bookings for a user within a specific week (Monday-Sunday).
     *
     * @param int $userId
     * @param string $dateInWeek Any date within the desired week
     */
    public function getByUserForWeek(int $userId, string $dateInWeek): Collection;

    /**
     * Get all bookings within a specific week (Monday-Sunday).
     *
     * @param string $dateInWeek Any date within the desired week
     */
    public function getForWeek(string $dateInWeek): Collection;

    /**
     * Check if a user has overlapping bookings for the given time range.
     *
     * @param int $userId
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeBookingId Booking ID to exclude (for updates)
     */
    public function hasOverlap(int $userId, string $startTime, string $endTime, ?int $excludeBookingId = null): bool;

    /**
     * Create a booking with overlap validation.
     *
     * @throws \App\Exceptions\BookingOverlapException
     */
    public function createWithOverlapCheck(array $data): Booking;

    /**
     * Update a booking with overlap validation.
     *
     * @throws \App\Exceptions\BookingOverlapException
     */
    public function updateWithOverlapCheck(int $id, array $data): Booking;
}
