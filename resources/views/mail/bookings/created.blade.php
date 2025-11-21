@extends('mail.layout')

@section('content')
    <h2>Бронирование создано</h2>
    
    <p>Здравствуйте, {{ $booking->user_name }}!</p>
    
    <p>Спасибо за ваше бронирование! Мы получили вашу заявку и обрабатываем её.</p>
    
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
            <span class="status-badge status-pending">Ожидает подтверждения</span>
        </div>
    </div>
    
    @if($booking->payment_status === 'pending' && $booking->payment_gateway?->value !== 'pay_on_arrival')
        <p><strong>Следующий шаг:</strong> Для завершения бронирования необходимо произвести оплату. Ссылка на оплату будет отправлена вам отдельным письмом.</p>
    @elseif($booking->payment_gateway?->value === 'pay_on_arrival')
        <p><strong>Оплата:</strong> Оплата производится при прибытии на место отправления.</p>
    @endif
    
    <p>Мы свяжемся с вами в ближайшее время для подтверждения бронирования и уточнения деталей.</p>
    
    <p>Спасибо за выбор Camp Events!</p>
@endsection

