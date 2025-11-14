<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeoLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $ipAddress = $request->ip();
        $config = $this->geoLocationService->getMapConfig($ipAddress);

        return response()->json([
            'success' => true,
            'data' => $config,
        ]);
    }

    /**
     * Проверить, должен ли пользователь использовать Яндекс Карты
     */
    public function checkProvider(Request $request): JsonResponse
    {
        $ipAddress = $request->ip();
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
        $ipAddress = $request->ip();
        $geoInfo = $this->geoLocationService->getGeoInfo($ipAddress);

        return response()->json([
            'success' => true,
            'data' => $geoInfo,
        ]);
    }
}
