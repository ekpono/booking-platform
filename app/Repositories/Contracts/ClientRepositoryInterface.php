<?php

namespace App\Repositories\Contracts;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a client by email.
     */
    public function findByEmail(string $email): ?Client;

    /**
     * Get clients with their bookings.
     */
    public function getWithBookings(): Collection;
}
