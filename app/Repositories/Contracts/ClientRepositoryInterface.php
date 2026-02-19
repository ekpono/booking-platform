<?php

namespace App\Repositories\Contracts;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

interface ClientRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a client by email.
     */
    public function findByEmail(string $email): ?Client;

    /**
     * Get clients with their bookings (paginated).
     */
    public function getWithBookings(int $perPage): LengthAwarePaginator;

    /**
     * Get clients for a specific user (paginated).
     */
    public function getByUserId(int $userId, int $perPage): LengthAwarePaginator;

    /**
     * Get clients with bookings for a specific user (paginated).
     */
    public function getWithBookingsByUserId(int $userId, int $perPage): LengthAwarePaginator;
}
