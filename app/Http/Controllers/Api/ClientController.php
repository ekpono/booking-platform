<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Resources\ClientResource;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository
    ) {}

    /**
     * Display a listing of clients.
     */
    public function index(): AnonymousResourceCollection
    {
        $clients = $this->clientRepository->all();

        return ClientResource::collection($clients);
    }

    /**
     * Store a newly created client.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientRepository->create($request->validated());

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified client.
     */
    public function show(int $id): ClientResource
    {
        $client = $this->clientRepository->findOrFail($id);

        return new ClientResource($client);
    }

    /**
     * Remove the specified client.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->clientRepository->delete($id);

        return response()->json(null, 204);
    }
}
