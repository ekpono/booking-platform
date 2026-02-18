<?php

namespace App\Repositories\Eloquent;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
     * Get clients with their bookings.
     */
    public function getWithBookings(): Collection
    {
        return $this->model->with('bookings')->get();
    }
}
