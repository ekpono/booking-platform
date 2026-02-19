<?php

namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository extends BaseRepository implements ClientRepositoryInterface
{
    public function __construct(Client $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a client by email.
     */
    public function findByEmail(string $email): ?Client
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get clients with their bookings (paginated).
     */
    public function getWithBookings(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('bookings')->paginate($perPage);
    }

    /**
     * Get clients for a specific user (paginated).
     */
    public function getByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->paginate($perPage);
    }

    /**
     * Get clients with bookings for a specific user (paginated).
     */
    public function getWithBookingsByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('bookings')
            ->paginate($perPage);
    }
}
