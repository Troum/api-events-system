@extends('mail.layout')

@section('content')
    <h2>Запрос на возврат средств получен</h2>
    
    <p>Здравствуйте, {{ $booking->user_name }}!</p>
    
    <p>Мы получили ваш запрос на возврат средств за бронирование.</p>
    
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
            <span class="info-label">Количество мест:</span>
            <span class="info-value">{{ $booking->seats }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Статус:</span>
            <span class="status-badge status-pending">Ожидает обработки</span>
        </div>
        
        @if($booking->refund_requested_at)
            <div class="info-row">
                <span class="info-label">Дата запроса:</span>
                <span class="info-value">{{ $booking->refund_requested_at->format('d.m.Y H:i') }}</span>
            </div>
        @endif
    </div>
    
    <p>Ваш запрос находится на рассмотрении. Мы обработаем его в течение 3-5 рабочих дней и свяжемся с вами по результатам.</p>
    
    <p>После одобрения возврата средства будут переведены на карту, с которой была произведена оплата, в течение 5-10 рабочих дней.</p>
    
    <p>Если у вас возникли вопросы, пожалуйста, свяжитесь с нами.</p>
@endsection

