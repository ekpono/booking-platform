<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Resources\ClientResource;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository
    ) {}

    /**
     * Display a listing of clients for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', config('app.pagination_limit', 12));
        $clients = $this->clientRepository->getByUserId(auth()->id(), $perPage);

        return ClientResource::collection($clients);
    }

    /**
     * Store a newly created client.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $client = $this->clientRepository->create($data);

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified client.
     *
     * @throws AuthorizationException
     */
    public function show(int $id): ClientResource
    {
        $client = $this->clientRepository->findOrFail($id);

        // Check if the client belongs to the authenticated user
        if ($client->user_id !== auth()->id()) {
            throw new AuthorizationException('You are not authorized to view this client.');
        }

        return new ClientResource($client);
    }

    /**
     * Remove the specified client.
     *
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $client = $this->clientRepository->findOrFail($id);

        // Check if the client belongs to the authenticated user
        if ($client->user_id !== auth()->id()) {
            throw new AuthorizationException('You are not authorized to delete this client.');
        }

        $this->clientRepository->delete($id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
