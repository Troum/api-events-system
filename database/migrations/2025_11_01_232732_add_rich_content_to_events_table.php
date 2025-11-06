<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Hero section
            $table->string('subtitle')->nullable()->after('title');
            $table->text('hero_description')->nullable()->after('description');
            $table->json('hero_images')->nullable(); // Массив изображений для слайдера
            
            // Основная информация
            $table->text('about')->nullable(); // Подробное описание "Что такое этот кэмп"
            $table->json('features')->nullable(); // Особенности (массив объектов с title, description, icon)
            
            // Программа
            $table->json('schedule')->nullable(); // Расписание по дням
            
            // Инфраструктура
            $table->json('infrastructure')->nullable(); // Объекты инфраструктуры (название, описание, изображения)
            
            // Тренерский состав / Команда
            $table->json('team')->nullable(); // Массив тренеров/организаторов
            
            // Пакеты и цены
            $table->json('packages')->nullable(); // Пакеты участия с разными опциями
            $table->json('not_included')->nullable(); // Что не входит в стоимость
            
            // Локация
            $table->string('venue_name')->nullable()->after('location'); // Название места проведения
            $table->text('venue_description')->nullable();
            $table->string('venue_address')->nullable();
            $table->decimal('venue_latitude', 10, 8)->nullable();
            $table->decimal('venue_longitude', 11, 8)->nullable();
            $table->string('airport_distance')->nullable();
            
            // Дополнительная информация
            $table->json('recommended_flights')->nullable(); // Рекомендованные рейсы
            $table->json('faq')->nullable(); // FAQ
            $table->json('gallery')->nullable(); // Галерея изображений
            
            // Контакты организатора
            $table->string('organizer_name')->nullable();
            $table->string('organizer_phone')->nullable();
            $table->string('organizer_email')->nullable();
            $table->string('organizer_telegram')->nullable();
            $table->string('organizer_whatsapp')->nullable();
            
            // Настройки отображения
            $table->boolean('show_booking_form')->default(true);
            $table->boolean('show_countdown')->default(false);
            $table->integer('max_participants')->nullable();
            $table->integer('current_participants')->default(0);
            
            // SEO и мета
            $table->string('slug')->unique()->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'subtitle',
                'hero_description',
                'hero_images',
                'about',
                'features',
                'schedule',
                'infrastructure',
                'team',
                'packages',
                'not_included',
                'venue_name',
                'venue_description',
                'venue_address',
                'venue_latitude',
                'venue_longitude',
                'airport_distance',
                'recommended_flights',
                'faq',
                'gallery',
                'organizer_name',
                'organizer_phone',
                'organizer_email',
                'organizer_telegram',
                'organizer_whatsapp',
                'show_booking_form',
                'show_countdown',
                'max_participants',
                'current_participants',
                'slug',
                'meta_description',
                'meta_keywords',
                'og_image',
            ]);
        });
    }
};
