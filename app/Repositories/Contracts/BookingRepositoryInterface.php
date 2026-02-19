<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all bookings for a specific user (paginated).
     */
    public function getByUserId(int $userId, int $perPage): LengthAwarePaginator;

    /**
     * Get all bookings for a specific client (paginated).
     */
    public function getByClientId(int $clientId, int $perPage): LengthAwarePaginator;

    /**
     * Get bookings for a user within a specific week (Monday-Sunday, paginated).
     *
     * @param int $userId
     * @param string $dateInWeek Any date within the desired week
     * @param int $perPage
     */
    public function getByUserForWeek(int $userId, string $dateInWeek, int $perPage): LengthAwarePaginator;

    /**
     * Get all bookings within a specific week (Monday-Sunday, paginated).
     *
     * @param string $dateInWeek Any date within the desired week
     * @param int $perPage
     */
    public function getForWeek(string $dateInWeek, int $perPage): LengthAwarePaginator;

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
