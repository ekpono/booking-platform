<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BookingOverlapException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // If week parameter is provided, return bookings for that week
        if ($request->has('week')) {
            $bookings = $this->bookingRepository->getForWeek($request->get('week'));

            return BookingResource::collection($bookings);
        }

        // Otherwise return all bookings
        $bookings = $this->bookingRepository->all();

        return BookingResource::collection($bookings->load(['user', 'client']));
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Use the authenticated user (for demo, we'll use user_id = 1 if not authenticated)
        $data['user_id'] = auth()->id() ?? 1;

        try {
            $booking = $this->bookingRepository->createWithOverlapCheck($data);

            return (new BookingResource($booking->load(['user', 'client'])))
                ->response()
                ->setStatusCode(201);
        } catch (BookingOverlapException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'overlap' => [$e->getMessage()],
                ],
            ], 422);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(int $id): BookingResource
    {
        $booking = $this->bookingRepository->findOrFail($id);

        return new BookingResource($booking->load(['user', 'client']));
    }

    /**
     * Update the specified booking.
     */
    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        try {
            $booking = $this->bookingRepository->updateWithOverlapCheck($id, $data);

            return (new BookingResource($booking->load(['user', 'client'])))
                ->response()
                ->setStatusCode(200);
        } catch (BookingOverlapException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'overlap' => [$e->getMessage()],
                ],
            ], 422);
        }
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->bookingRepository->delete($id);

        return response()->json(null, 204);
    }
}
