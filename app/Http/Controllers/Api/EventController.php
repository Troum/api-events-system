<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
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

    public function show(Event $event): JsonResponse
    {
        $event->load(['trips', 'teamMembers', 'eventPackages']);

        return response()->json(['data' => $event]);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $this->eventService->create($request->validated());

        return response()->json(['data' => $event], 201);
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $updated = $this->eventService->updateBySlug($event->slug, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json(['message' => 'Event updated successfully']);
    }

    public function destroy(Event $event): JsonResponse
    {
        $deleted = $this->eventService->deleteBySlug($event->slug);

        if (! $deleted) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
