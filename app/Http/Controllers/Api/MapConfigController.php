<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeoLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapConfigController extends Controller
{
    public function __construct(
        private readonly GeoLocationService $geoLocationService
    ) {}

    /**
     * Получить конфигурацию карт на основе геолокации пользователя
     */
    public function index(Request $request): JsonResponse
    {
        // Получаем IP из различных источников
        $ipAddress = $this->getClientIp($request);
        
        $config = $this->geoLocationService->getMapConfig($ipAddress);

        return response()->json([
            'success' => true,
            'data' => $config,
        ]);
    }

    /**
     * Получить реальный IP клиента
     */
    private function getClientIp(Request $request): string
    {
        // Список заголовков для проверки (в порядке приоритета)
        $headers = [
            'CF-Connecting-IP',     // Cloudflare
            'X-Real-IP',            // Nginx proxy
            'X-Forwarded-For',      // Стандартный прокси заголовок
            'X-Client-IP',          // Кастомный заголовок
            'X-Forwarded',          // Альтернативный
            'Forwarded-For',        // Альтернативный
            'Forwarded',            // Стандартный RFC 7239
        ];

        foreach ($headers as $header) {
            $ip = $request->header($header);
            if ($ip) {
                // X-Forwarded-For может содержать несколько IP через запятую
                $ips = explode(',', $ip);
                $ip = trim($ips[0]);
                
                // Проверяем, что это валидный IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Если ничего не найдено, используем стандартный метод Laravel
        $ip = $request->ip();
        
        // Если это локальный IP, пробуем получить через внешний API
        if ($this->isLocalIp($ip)) {
            try {
                $externalIp = Http::timeout(2)->get('https://api.ipify.org?format=json');
                if ($externalIp->successful()) {
                    $externalIpAddress = $externalIp->json('ip');
                    if ($externalIpAddress && filter_var($externalIpAddress, FILTER_VALIDATE_IP)) {
                        return $externalIpAddress;
                    }
                }
            } catch (\Exception $e) {
                // Игнорируем ошибку, используем fallback
            }
        }

        return $ip;
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
     * Проверить, должен ли пользователь использовать Яндекс Карты
     */
    public function checkProvider(Request $request): JsonResponse
    {
        $ipAddress = $this->getClientIp($request);
        
        $provider = $this->geoLocationService->getMapProvider($ipAddress);
        $useYandex = $provider === 'yandex';

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'use_yandex_maps' => $useYandex,
                'use_google_maps' => ! $useYandex,
            ],
        ]);
    }

    /**
     * Получить информацию о геолокации пользователя
     */
    public function geoInfo(Request $request): JsonResponse
    {
        $ipAddress = $this->getClientIp($request);
        
        $geoInfo = $this->geoLocationService->getGeoInfo($ipAddress);

        return response()->json([
            'success' => true,
            'data' => $geoInfo,
        ]);
    }
}
