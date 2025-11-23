<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Получить контактные данные
     */
    public function contacts(): JsonResponse
    {
        $contacts = Setting::getContactData();

        return response()->json([
            'success' => true,
            'data' => [
                'phone' => $contacts['contact_phone'] ?? null,
                'email' => $contacts['contact_email'] ?? null,
                'address' => $contacts['contact_address'] ?? null,
                'telegram' => $contacts['contact_telegram'] ?? null,
                'whatsapp' => $contacts['contact_whatsapp'] ?? null,
                'instagram' => $contacts['contact_instagram'] ?? null,
                'vk' => $contacts['contact_vk'] ?? null,
                'facebook' => $contacts['contact_facebook'] ?? null,
            ],
        ]);
    }

    /**
     * Получить все настройки сайта
     */
    public function index(): JsonResponse
    {
        $settings = [
            'contact' => Setting::getGroup('contact'),
            'general' => Setting::getGroup('general'),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
