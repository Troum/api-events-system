<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Основная информация
            $table->string('title')->nullable()->after('event_id');
            $table->text('description')->nullable();
            $table->json('images')->nullable(); // Галерея изображений поездки
            
            // Маршрут и логистика
            $table->string('city_to')->nullable()->after('city_from'); // Город назначения
            $table->string('transport_type')->nullable(); // Тип транспорта (автобус, микроавтобус, самолет)
            $table->text('route_description')->nullable(); // Описание маршрута
            $table->time('arrival_time')->nullable(); // Время прибытия
            $table->string('duration')->nullable(); // Продолжительность поездки
            $table->json('stops')->nullable(); // Остановки по пути
            
            // Что включено
            $table->json('includes')->nullable(); // Что входит в стоимость поездки
            $table->json('not_includes')->nullable(); // Что не входит
            
            // Удобства в транспорте
            $table->json('amenities')->nullable(); // Удобства (Wi-Fi, кондиционер, розетки и т.д.)
            
            // Багаж
            $table->string('luggage_allowance')->nullable(); // Разрешенный багаж
            $table->text('luggage_rules')->nullable(); // Правила провоза багажа
            
            // Точки посадки/высадки
            $table->json('pickup_points')->nullable(); // Точки посадки с адресами и временем
            $table->json('dropoff_points')->nullable(); // Точки высадки
            
            // Контакты водителя/гида
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('guide_name')->nullable();
            $table->string('guide_phone')->nullable();
            
            // Дополнительные услуги
            $table->json('additional_services')->nullable(); // Доп. услуги (питание в пути, экскурсии)
            
            // Правила и условия
            $table->text('cancellation_policy')->nullable(); // Политика отмены
            $table->text('terms_and_conditions')->nullable(); // Условия
            $table->integer('min_age')->nullable(); // Минимальный возраст
            $table->text('requirements')->nullable(); // Требования к участникам
            
            // Статус и настройки
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->boolean('is_featured')->default(false); // Рекомендуемая поездка
            $table->boolean('allow_waitlist')->default(false); // Разрешить лист ожидания
            $table->integer('waitlist_count')->default(0); // Количество в листе ожидания
            
            // Скидки и акции
            $table->decimal('early_bird_price', 10, 2)->nullable(); // Цена раннего бронирования
            $table->date('early_bird_deadline')->nullable(); // Дедлайн ранней цены
            $table->json('discounts')->nullable(); // Скидки (групповые, студенческие и т.д.)
            
            // Рейтинг и отзывы
            $table->decimal('rating', 3, 2)->nullable(); // Средний рейтинг
            $table->integer('reviews_count')->default(0); // Количество отзывов
            
            // SEO
            $table->string('slug')->unique()->nullable();
            $table->text('meta_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'description',
                'images',
                'city_to',
                'transport_type',
                'route_description',
                'arrival_time',
                'duration',
                'stops',
                'includes',
                'not_includes',
                'amenities',
                'luggage_allowance',
                'luggage_rules',
                'pickup_points',
                'dropoff_points',
                'driver_name',
                'driver_phone',
                'guide_name',
                'guide_phone',
                'additional_services',
                'cancellation_policy',
                'terms_and_conditions',
                'min_age',
                'requirements',
                'status',
                'is_featured',
                'allow_waitlist',
                'waitlist_count',
                'early_bird_price',
                'early_bird_deadline',
                'discounts',
                'rating',
                'reviews_count',
                'slug',
                'meta_description',
            ]);
        });
    }
};
