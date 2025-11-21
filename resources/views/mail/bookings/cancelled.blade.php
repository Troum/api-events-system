@extends('mail.layout')

@section('content')
    <h2>Бронирование отменено</h2>
    
    <p>Здравствуйте, {{ $booking->user_name }}!</p>
    
    <p>К сожалению, ваше бронирование было отменено.</p>
    
    <div class="booking-info">
        <h3>Детали отмененного бронирования</h3>
        
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
            <span class="status-badge status-cancelled">Отменено</span>
        </div>
        
        @if($booking->cancelled_at)
            <div class="info-row">
                <span class="info-label">Дата отмены:</span>
                <span class="info-value">{{ $booking->cancelled_at->format('d.m.Y H:i') }}</span>
            </div>
        @endif
        
        @if($booking->cancellation_reason)
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                <span class="info-label">Причина отмены:</span>
                <p style="margin-top: 5px; color: #666;">{{ $booking->cancellation_reason }}</p>
            </div>
        @endif
    </div>
    
    @if($booking->payment_status === 'paid')
        <p><strong>Важно:</strong> Если вы уже произвели оплату, средства будут возвращены в течение 5-10 рабочих дней на карту, с которой была произведена оплата.</p>
    @endif
    
    <p>Если у вас возникли вопросы или вы хотите забронировать другое мероприятие, пожалуйста, свяжитесь с нами.</p>
    
    <p>Спасибо за понимание!</p>
@endsection

