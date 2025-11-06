<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $events = $this->eventService->getAll(['trips', 'teamMembers', 'eventPackages']);

        return response()->json(['data' => $events]);
    }

    public function show(int $id): JsonResponse
    {
        $event = $this->eventService->getById($id, ['trips', 'teamMembers', 'eventPackages']);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json(['data' => $event]);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $this->eventService->create($request->validated());

        return response()->json(['data' => $event], 201);
    }

    public function update(UpdateEventRequest $request, int $id): JsonResponse
    {
        $updated = $this->eventService->update($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json(['message' => 'Event updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->eventService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
