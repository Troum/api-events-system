<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * Страны СНГ (Содружество Независимых Государств)
     */
    private const CIS_COUNTRIES = [
        'RU', // Россия
        'BY', // Беларусь
        'KZ', // Казахстан
        'AM', // Армения
        'AZ', // Азербайджан
        'KG', // Киргизия
        'MD', // Молдова
        'TJ', // Таджикистан
        'TM', // Туркменистан
        'UZ', // Узбекистан
    ];

    /**
     * Страны ШОС (Шанхайская организация сотрудничества)
     */
    private const SCO_COUNTRIES = [
        'CN', // Китай
        'IN', // Индия
        'PK', // Пакистан
        'IR', // Иран
    ];

    /**
     * Страны БРИКС
     */
    private const BRICS_COUNTRIES = [
        'BR', // Бразилия
        'RU', // Россия (уже в СНГ)
        'IN', // Индия (уже в ШОС)
        'CN', // Китай (уже в ШОС)
        'ZA', // ЮАР
        'EG', // Египет
        'ET', // Эфиопия
        'IR', // Иран (уже в ШОС)
        'SA', // Саудовская Аравия
        'AE', // ОАЭ
    ];

    /**
     * Определяет, должен ли пользователь использовать Яндекс Карты
     */
    public function shouldUseYandexMaps(?string $ipAddress = null): bool
    {
        $countryCode = $this->getCountryCode($ipAddress);

        if (! $countryCode) {
            // По умолчанию используем Яндекс Карты для неопределенных регионов
            return true;
        }

        return $this->isYandexMapsRegion($countryCode);
    }

    /**
     * Проверяет, относится ли страна к региону Яндекс Карт
     */
    public function isYandexMapsRegion(string $countryCode): bool
    {
        $allYandexRegions = array_unique(array_merge(
            self::CIS_COUNTRIES,
            self::SCO_COUNTRIES,
            self::BRICS_COUNTRIES
        ));

        return in_array(strtoupper($countryCode), $allYandexRegions);
    }

    /**
     * Получает код страны по IP адресу
     */
    public function getCountryCode(?string $ipAddress = null): ?string
    {
        $ipAddress = $ipAddress ?? request()->ip();

        // Для локальных IP возвращаем RU
        if ($this->isLocalIp($ipAddress)) {
            return 'RU';
        }

        // Кешируем результат на 24 часа
        return Cache::remember("geo_country_{$ipAddress}", 60 * 60 * 24, function () use ($ipAddress) {
            return $this->fetchCountryCode($ipAddress);
        });
    }

    /**
     * Получает полную информацию о геолокации
     */
    public function getGeoInfo(?string $ipAddress = null): array
    {
        $ipAddress = $ipAddress ?? request()->ip();

        if ($this->isLocalIp($ipAddress)) {
            return [
                'country_code' => 'RU',
                'country_name' => 'Russia',
                'city' => 'Moscow',
                'use_yandex_maps' => true,
            ];
        }

        return Cache::remember("geo_info_{$ipAddress}", 60 * 60 * 24, function () use ($ipAddress) {
            $info = $this->fetchGeoInfo($ipAddress);
            $info['use_yandex_maps'] = $this->isYandexMapsRegion($info['country_code'] ?? '');

            return $info;
        });
    }

    /**
     * Проверяет, является ли IP локальным
     */
    private function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost'])
            || str_starts_with($ip, '192.168.')
            || str_starts_with($ip, '10.')
            || str_starts_with($ip, '172.');
    }

    /**
     * Получает код страны через внешний API
     */
    private function fetchCountryCode(string $ipAddress): ?string
    {
        try {
            // Используем бесплатный API ip-api.com (без ключа, лимит 45 req/min)
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ipAddress}", [
                'fields' => 'countryCode',
            ]);

            if ($response->successful()) {
                return $response->json('countryCode');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch country code', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Получает полную геоинформацию через внешний API
     */
    private function fetchGeoInfo(string $ipAddress): array
    {
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ipAddress}", [
                'fields' => 'status,country,countryCode,city,lat,lon',
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'country_code' => $response->json('countryCode'),
                    'country_name' => $response->json('country'),
                    'city' => $response->json('city'),
                    'latitude' => $response->json('lat'),
                    'longitude' => $response->json('lon'),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch geo info', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'country_code' => null,
            'country_name' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }

    /**
     * Получает рекомендуемый провайдер карт
     */
    public function getMapProvider(?string $ipAddress = null): string
    {
        return $this->shouldUseYandexMaps($ipAddress) ? 'yandex' : 'openstreetmap';
    }

    /**
     * Получает конфигурацию для карт
     */
    public function getMapConfig(?string $ipAddress = null): array
    {
        $provider = $this->getMapProvider($ipAddress);
        $geoInfo = $this->getGeoInfo($ipAddress);

        return [
            'provider' => $provider,
            'country_code' => $geoInfo['country_code'],
            'country_name' => $geoInfo['country_name'],
            'city' => $geoInfo['city'],
            'api_key' => $provider === 'yandex'
                ? config('services.yandex_maps.api_key')
                : null, // OpenStreetMap не требует API ключа
        ];
    }
}
