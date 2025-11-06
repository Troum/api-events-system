<?php

namespace App\Http\Requests;

use App\Enums\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trip_id' => 'required|exists:trips,id',
            'user_name' => 'required|string|max:255',
            'user_phone' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
            'seats' => 'required|integer|min:1',
            'payment_gateway' => [
                'nullable',
                Rule::enum(PaymentGateway::class),
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return;
                    }

                    $trip = \App\Models\Trip::find($this->trip_id);
                    
                    if (!$trip) {
                        return;
                    }

                    // Если у поездки не указаны доступные способы оплаты, разрешаем только pay_on_arrival
                    if (!$trip->available_payment_gateways || empty($trip->available_payment_gateways)) {
                        if ($value !== 'pay_on_arrival') {
                            $fail('Для этой поездки доступна только оплата по факту.');
                        }
                        return;
                    }

                    // Проверяем, что выбранный способ оплаты доступен для этой поездки
                    if (!in_array($value, $trip->available_payment_gateways)) {
                        $fail('Выбранный способ оплаты недоступен для этой поездки.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'trip_id' => 'поездка',
            'user_name' => 'имя',
            'user_phone' => 'телефон',
            'user_email' => 'email',
            'seats' => 'количество мест',
            'payment_gateway' => 'способ оплаты',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'trip_id.exists' => 'Выбранная поездка не найдена',
            'seats.min' => 'Необходимо забронировать минимум 1 место',
        ];
    }
}
