@extends('mail.layout')

@section('content')
    <h2>Бронирование подтверждено!</h2>
    
    <p>Здравствуйте, {{ $booking->user_name }}!</p>
    
    <p>Мы рады сообщить, что ваше бронирование успешно подтверждено.</p>
    
    <div class="booking-info">
        <h3>Детали бронирования</h3>
        
        <div class="info-row">
            <span class="info-label">Мероприятие:</span>
            <span class="info-value">{{ $booking->trip->event->title }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Поездка:</span>
            <span class="info-value">{{ $booking->trip->title }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Маршрут:</span>
            <span class="info-value">{{ $booking->trip->city_from }} → {{ $booking->trip->city_to }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Дата отправления:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($booking->trip->departure_time)->format('d.m.Y H:i') }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Количество мест:</span>
            <span class="info-value">{{ $booking->seats }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Статус:</span>
            <span class="status-badge status-confirmed">Подтверждено</span>
        </div>
    </div>
    
    <p>Ваше бронирование активно и готово к поездке. Мы свяжемся с вами перед отправлением для уточнения деталей.</p>
    
    @if($booking->trip->driver_name || $booking->trip->driver_phone)
        <div class="booking-info">
            <h3>Контактная информация</h3>
            @if($booking->trip->driver_name)
                <div class="info-row">
                    <span class="info-label">Водитель:</span>
                    <span class="info-value">{{ $booking->trip->driver_name }}</span>
                </div>
            @endif
            @if($booking->trip->driver_phone)
                <div class="info-row">
                    <span class="info-label">Телефон водителя:</span>
                    <span class="info-value">{{ $booking->trip->driver_phone }}</span>
                </div>
            @endif
        </div>
    @endif
    
    <p>Спасибо за выбор Camp Events! Желаем вам приятной поездки!</p>
@endsection

