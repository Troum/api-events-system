# 🏗️ Repository Pattern Architecture Implementation

## ✅ Что уже создано

### 1. Миграции
- ✅ `create_team_members_table` - таблица членов команды
- ✅ `create_event_team_table` - связь событий и команды (many-to-many)
- ✅ `create_event_packages_table` - пакеты событий

### 2. Модели
- ✅ `TeamMember` - с relationships к Event
- ✅ `EventPackage` - с relationships к Event и Booking
- ✅ `Event` - обновлена с relationships к TeamMember и EventPackage

### 3. Интерфейсы репозиториев
- ✅ `RepositoryInterface` - базовый интерфейс
- ✅ `EventRepositoryInterface` - специфичный для Event

## 📋 Что нужно создать дальше

### Шаг 1: Создать остальные интерфейсы репозиториев

```bash
# app/Repositories/Contracts/TeamMemberRepositoryInterface.php
# app/Repositories/Contracts/EventPackageRepositoryInterface.php
# app/Repositories/Contracts/TripRepositoryInterface.php
# app/Repositories/Contracts/BookingRepositoryInterface.php
```

### Шаг 2: Создать базовый репозиторий

```php
// app/Repositories/BaseRepository.php
abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;
    
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }
    
    // ... остальные методы
}
```

### Шаг 3: Создать конкретные репозитории

```bash
# app/Repositories/EventRepository.php extends BaseRepository implements EventRepositoryInterface
# app/Repositories/TeamMemberRepository.php
# app/Repositories/EventPackageRepository.php
# app/Repositories/TripRepository.php
# app/Repositories/BookingRepository.php
```

### Шаг 4: Создать сервисы

```bash
# app/Services/EventService.php
# app/Services/TeamMemberService.php
# app/Services/EventPackageService.php
# app/Services/TripService.php
# app/Services/BookingService.php
```

### Шаг 5: Создать RepositoryServiceProvider

```php
// app/Providers/RepositoryServiceProvider.php
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Event
        $this->app->when(EventService::class)
            ->needs(EventRepositoryInterface::class)
            ->give(EventRepository::class);
            
        // TeamMember
        $this->app->when(TeamMemberService::class)
            ->needs(TeamMemberRepositoryInterface::class)
            ->give(TeamMemberRepository::class);
            
        // EventPackage
        $this->app->when(EventPackageService::class)
            ->needs(EventPackageRepositoryInterface::class)
            ->give(EventPackageRepository::class);
            
        // Trip
        $this->app->when(TripService::class)
            ->needs(TripRepositoryInterface::class)
            ->give(TripRepository::class);
            
        // Booking
        $this->app->when(BookingService::class)
            ->needs(BookingRepositoryInterface::class)
            ->give(BookingRepository::class);
    }
}
```

### Шаг 6: Обновить контроллеры

```php
// app/Http/Controllers/Api/EventController.php
class EventController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}
    
    public function index(Request $request)
    {
        $events = $this->eventService->getPaginated(
            perPage: $request->input('per_page', 15),
            withRelations: ['trips', 'teamMembers', 'eventPackages']
        );
        
        return response()->json($events);
    }
}
```

### Шаг 7: Создать Filament Resources

```bash
php artisan make:filament-resource TeamMember --generate
php artisan make:filament-resource EventPackage --generate
```

### Шаг 8: Обновить EventResource в Filament

Добавить RelationManager для:
- Team Members (many-to-many)
- Event Packages (one-to-many)

## 🎯 Архитектура

```
Request
  ↓
Controller (тонкий слой)
  ↓
Service (бизнес-логика)
  ↓
Repository (работа с БД)
  ↓
Model
  ↓
Database
```

## 📝 Пример полной реализации

### EventRepository.php

```php
<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    public function __construct(Event $model)
    {
        $this->model = $model;
    }

    public function getWithTrips(): Collection
    {
        return $this->model->with('trips')->get();
    }

    public function getWithTeam(): Collection
    {
        return $this->model->with('teamMembers')->get();
    }

    public function getWithPackages(): Collection
    {
        return $this->model->with('eventPackages')->get();
    }

    public function findBySlug(string $slug): ?Event
    {
        return $this->model
            ->with(['trips', 'teamMembers', 'eventPackages'])
            ->where('slug', $slug)
            ->first();
    }

    public function getUpcoming(int $limit = null): Collection
    {
        $query = $this->model
            ->where('date_start', '>=', now())
            ->orderBy('date_start');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getPast(int $limit = null): Collection
    {
        $query = $this->model
            ->where('date_end', '<', now())
            ->orderBy('date_end', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
```

### EventService.php

```php
<?php

namespace App\Services;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventService
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {}

    public function getPaginated(int $perPage = 15, array $withRelations = []): LengthAwarePaginator
    {
        return $this->eventRepository->paginate($perPage, ['*'], $withRelations);
    }

    public function getById(int $id, array $withRelations = []): ?Event
    {
        return $this->eventRepository->find($id, ['*'], $withRelations);
    }

    public function getBySlug(string $slug): ?Event
    {
        return $this->eventRepository->findBySlug($slug);
    }

    public function getUpcoming(int $limit = null): Collection
    {
        return $this->eventRepository->getUpcoming($limit);
    }

    public function create(array $data): Event
    {
        return $this->eventRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->eventRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->eventRepository->delete($id);
    }

    public function attachTeamMember(int $eventId, int $teamMemberId, array $pivotData = []): void
    {
        $event = $this->eventRepository->findOrFail($eventId);
        $event->teamMembers()->attach($teamMemberId, $pivotData);
    }

    public function detachTeamMember(int $eventId, int $teamMemberId): void
    {
        $event = $this->eventRepository->findOrFail($eventId);
        $event->teamMembers()->detach($teamMemberId);
    }
}
```

### EventController.php (тонкий)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $events = $this->eventService->getPaginated(
            perPage: $request->input('per_page', 15),
            withRelations: ['trips', 'teamMembers', 'eventPackages']
        );

        return response()->json($events);
    }

    public function show(int $id): JsonResponse
    {
        $event = $this->eventService->getById($id, ['trips', 'teamMembers', 'eventPackages']);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json(['data' => $event]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
            'location' => 'required|string',
            // ... остальные правила
        ]);

        $event = $this->eventService->create($validated);

        return response()->json(['data' => $event], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            // ... остальные правила
        ]);

        $updated = $this->eventService->update($id, $validated);

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
```

## 🚀 Следующие шаги

1. Запустить миграции: `php artisan migrate`
2. Создать все файлы по примерам выше
3. Зарегистрировать RepositoryServiceProvider в `config/app.php`
4. Обновить Filament Resources
5. Протестировать API

## 📌 Важные моменты

- ✅ Контроллеры теперь тонкие - только валидация и вызов сервиса
- ✅ Вся бизнес-логика в сервисах
- ✅ Работа с БД только через репозитории
- ✅ DI через интерфейсы для легкого тестирования
- ✅ Можно легко заменить реализацию репозитория

Хотите, чтобы я создал все оставшиеся файлы? Это займет несколько сообщений из-за объема кода.

