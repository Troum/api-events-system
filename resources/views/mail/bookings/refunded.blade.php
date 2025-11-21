@extends('mail.layout')

@section('content')
    <h2>Возврат средств выполнен</h2>
    
    <p>Здравствуйте, {{ $booking->user_name }}!</p>
    
    <p>Мы рады сообщить, что возврат средств за ваше бронирование успешно выполнен.</p>
    
    <div class="booking-info">
        <h3>Детали возврата</h3>
        
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
            <span class="info-label">Количество мест:</span>
            <span class="info-value">{{ $booking->seats }}</span>
        </div>
        
        @if($booking->refund_amount)
            <div class="info-row">
                <span class="info-label">Сумма возврата:</span>
                <span class="info-value" style="font-weight: 600; color: #667eea;">{{ number_format($booking->refund_amount, 2, ',', ' ') }} ₽</span>
            </div>
        @endif
        
        <div class="info-row">
            <span class="info-label">Статус:</span>
            <span class="status-badge status-refunded">Возвращено</span>
        </div>
        
        @if($booking->refunded_at)
            <div class="info-row">
                <span class="info-label">Дата возврата:</span>
                <span class="info-value">{{ $booking->refunded_at->format('d.m.Y H:i') }}</span>
            </div>
        @endif
    </div>
    
    <p>Средства будут зачислены на карту, с которой была произведена оплата, в течение 5-10 рабочих дней.</p>
    
    <p>Если у вас возникли вопросы или средства не поступили в указанный срок, пожалуйста, свяжитесь с нами.</p>
    
    <p>Спасибо за понимание!</p>
@endsection

