<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug',
            'description' => 'required|string',
            'hero_description' => 'nullable|string',
            'image' => 'nullable|string',
            'hero_images' => 'nullable|array',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after:date_start',
            'location' => 'required|string|max:255',
            'about' => 'nullable|string',
            'features' => 'nullable|array',
            'schedule' => 'nullable|array',
            'infrastructure' => 'nullable|array',
            'venue_name' => 'nullable|string',
            'venue_description' => 'nullable|string',
            'venue_address' => 'nullable|string',
            'venue_latitude' => 'nullable|numeric',
            'venue_longitude' => 'nullable|numeric',
            'organizer_name' => 'nullable|string',
            'organizer_phone' => 'nullable|string',
            'organizer_email' => 'nullable|email',
            'show_booking_form' => 'nullable|boolean',
            'show_countdown' => 'nullable|boolean',
            'max_participants' => 'nullable|integer|min:1',
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
            'title' => 'название',
            'subtitle' => 'подзаголовок',
            'slug' => 'URL-адрес',
            'description' => 'описание',
            'hero_description' => 'описание для главного экрана',
            'image' => 'изображение',
            'hero_images' => 'изображения для слайдера',
            'date_start' => 'дата начала',
            'date_end' => 'дата окончания',
            'location' => 'локация',
            'about' => 'о мероприятии',
            'features' => 'особенности',
            'schedule' => 'расписание',
            'infrastructure' => 'инфраструктура',
            'venue_name' => 'название площадки',
            'venue_description' => 'описание площадки',
            'venue_address' => 'адрес площадки',
            'venue_latitude' => 'широта',
            'venue_longitude' => 'долгота',
            'organizer_name' => 'имя организатора',
            'organizer_phone' => 'телефон организатора',
            'organizer_email' => 'email организатора',
            'show_booking_form' => 'показывать форму бронирования',
            'show_countdown' => 'показывать обратный отсчет',
            'max_participants' => 'максимум участников',
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
            'date_end.after' => 'Дата окончания должна быть позже даты начала',
        ];
    }
}
