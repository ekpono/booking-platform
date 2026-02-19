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
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Display a listing of bookings for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', config('app.pagination_limit', 12));

        // If week parameter is provided, return bookings for that week
        if ($request->has('week')) {
            $bookings = $this->bookingRepository->getByUserForWeek(
                auth()->id(),
                $request->get('week'),
                $perPage
            );

            return BookingResource::collection($bookings);
        }

        // Otherwise return all bookings for the authenticated user
        $bookings = $this->bookingRepository->getByUserId(auth()->id(), $perPage);

        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['user_id'] = auth()->id();

        try {
            $booking = $this->bookingRepository->createWithOverlapCheck($data);

            return (new BookingResource($booking->load(['user', 'client'])))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (BookingOverlapException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'overlap' => [$e->getMessage()],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(int $id): BookingResource
    {
        $booking = $this->bookingRepository->findOrFail($id);

        $this->authorize('view', $booking);

        return new BookingResource($booking->load(['user', 'client']));
    }

    /**
     * Update the specified booking.
     */
    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findOrFail($id);

        $this->authorize('update', $booking);

        $data = $request->validated();

        try {
            $booking = $this->bookingRepository->updateWithOverlapCheck($id, $data);

            return (new BookingResource($booking->load(['user', 'client'])))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (BookingOverlapException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'overlap' => [$e->getMessage()],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->findOrFail($id);

        $this->authorize('delete', $booking);

        $this->bookingRepository->delete($id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
