<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case YOOKASSA = 'yookassa';
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
    case WEBPAY = 'webpay';
    case PAY_ON_ARRIVAL = 'pay_on_arrival';

    /**
     * Получить метку для отображения
     */
    public function label(): string
    {
        return match ($this) {
            self::YOOKASSA => 'ЮKassa',
            self::STRIPE => 'Stripe',
            self::PAYPAL => 'PayPal',
            self::WEBPAY => 'WebPay',
            self::PAY_ON_ARRIVAL => 'Оплата по факту',
        };
    }

    /**
     * Получить описание
     */
    public function description(): string
    {
        return match ($this) {
            self::YOOKASSA => 'Банковская карта, СБП',
            self::STRIPE => 'Международные карты',
            self::PAYPAL => 'PayPal аккаунт',
            self::WEBPAY => 'Онлайн платежи',
            self::PAY_ON_ARRIVAL => 'Оплата при встрече с водителем',
        };
    }

    /**
     * Проверить, требуется ли онлайн оплата
     */
    public function requiresOnlinePayment(): bool
    {
        return $this !== self::PAY_ON_ARRIVAL;
    }

    /**
     * Получить все опции для select
     */
    public static function options(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}
