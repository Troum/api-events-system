<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(
        private TripService $tripService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Если указан event_id, возвращаем поездки для конкретного события
        if ($request->has('event_id')) {
            $trips = $this->tripService->getByEventId($request->input('event_id'));
            return response()->json(['data' => $trips]);
        }

        // Иначе возвращаем все поездки с пагинацией
        $trips = $this->tripService->getPaginated(
            perPage: $request->input('per_page', 15),
            withRelations: ['event']
        );

        return response()->json($trips);
    }

    public function show(int $id): JsonResponse
    {
        $trip = $this->tripService->getById($id, ['event']);

        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        return response()->json(['data' => $trip]);
    }

    public function store(StoreTripRequest $request): JsonResponse
    {
        $trip = $this->tripService->create($request->validated());

        return response()->json(['data' => $trip], 201);
    }

    public function update(UpdateTripRequest $request, int $id): JsonResponse
    {
        $updated = $this->tripService->update($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        return response()->json(['message' => 'Trip updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->tripService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Trip not found'], 404);
        }

        return response()->json(['message' => 'Trip deleted successfully']);
    }
}
