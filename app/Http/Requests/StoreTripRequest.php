<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
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
            'event_id' => 'required|exists:events,id',
            'city_from' => 'required|string|max:255',
            'city_to' => 'nullable|string|max:255',
            'departure_time' => 'required|date',
            'arrival_time' => 'nullable|date|after:departure_time',
            'duration' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'seats_total' => 'required|integer|min:1',
            'seats_taken' => 'sometimes|integer|min:0|lte:seats_total',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'transport_type' => 'nullable|string|in:bus,minibus,car,train,plane',
            'route_description' => 'nullable|string',
            'stops' => 'nullable|array',
            'includes' => 'nullable|array',
            'not_includes' => 'nullable|array',
            'amenities' => 'nullable|array',
            'luggage_allowance' => 'nullable|string',
            'luggage_rules' => 'nullable|string',
            'pickup_points' => 'nullable|array',
            'dropoff_points' => 'nullable|array',
            'driver_name' => 'nullable|string',
            'driver_phone' => 'nullable|string',
            'guide_name' => 'nullable|string',
            'guide_phone' => 'nullable|string',
            'additional_services' => 'nullable|array',
            'cancellation_policy' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'min_age' => 'nullable|integer|min:0',
            'requirements' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published,cancelled,completed',
            'is_featured' => 'nullable|boolean',
            'allow_waitlist' => 'nullable|boolean',
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
            'event_id' => 'событие',
            'city_from' => 'город отправления',
            'city_to' => 'город назначения',
            'departure_time' => 'время отправления',
            'arrival_time' => 'время прибытия',
            'duration' => 'длительность',
            'price' => 'цена',
            'seats_total' => 'всего мест',
            'seats_taken' => 'занято мест',
            'description' => 'описание',
            'images' => 'изображения',
            'transport_type' => 'тип транспорта',
            'route_description' => 'описание маршрута',
            'stops' => 'остановки',
            'includes' => 'включено',
            'not_includes' => 'не включено',
            'amenities' => 'удобства',
            'luggage_allowance' => 'норма багажа',
            'luggage_rules' => 'правила багажа',
            'pickup_points' => 'точки посадки',
            'dropoff_points' => 'точки высадки',
            'driver_name' => 'имя водителя',
            'driver_phone' => 'телефон водителя',
            'guide_name' => 'имя гида',
            'guide_phone' => 'телефон гида',
            'additional_services' => 'дополнительные услуги',
            'cancellation_policy' => 'политика отмены',
            'terms_and_conditions' => 'условия',
            'min_age' => 'минимальный возраст',
            'requirements' => 'требования',
            'status' => 'статус',
            'is_featured' => 'рекомендуемая',
            'allow_waitlist' => 'разрешить лист ожидания',
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
            'arrival_time.after' => 'Время прибытия должно быть позже времени отправления',
            'seats_taken.lte' => 'Занято мест не может быть больше общего количества мест',
        ];
    }
}
