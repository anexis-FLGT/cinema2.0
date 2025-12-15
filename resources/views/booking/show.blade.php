@extends('layouts.app')

@section('title', 'Бронирование билетов - ' . $movie->movie_title)

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/booking.css') }}">
<style>
    /* Убираем анимацию поднятия у карточки со схемой зала */
    .card:hover {
        transform: none !important;
    }
</style>

<div class="container my-5" style="color: var(--text-primary);">
    {{-- Информация о фильме и сеансе --}}
    <div class="row mb-5">
        <div class="col-md-4">
            <img src="{{ $movie->poster }}" alt="{{ $movie->movie_title }}" class="img-fluid rounded shadow-lg">
        </div>
        <div class="col-md-8">
            <h1 class="mb-3">{{ $movie->movie_title }}</h1>
            
            @if($movie->genres && $movie->genres->count() > 0)
                <p class="mb-2"><strong>Жанр:</strong> {{ $movie->genres->pluck('genre_name')->join(', ') }}</p>
            @endif
            
            <p class="mb-2"><strong>Возрастное ограничение:</strong> {{ $movie->age_limit }}</p>
            <p class="mb-2"><strong>Длительность:</strong> {{ $movie->duration }}</p>
            
            {{-- Информация о сеансе --}}
            <div class="mt-4 p-3 rounded" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
                <h5 class="mb-3" style="color: var(--text-primary);">Информация о сеансе</h5>
                <p class="mb-2">
                    <strong><i class="bi bi-calendar-event me-2"></i>Дата и время:</strong> 
                    {{ \Carbon\Carbon::parse($session->date_time_session)->locale('ru')->isoFormat('D MMMM YYYY, dddd, HH:mm') }}
                </p>
                <p class="mb-0">
                    <strong><i class="bi bi-door-open me-2"></i>Зал:</strong> 
                    {{ $hall->hall_name }} ({{ $hall->type_hall }})
                </p>
            </div>
        </div>
    </div>

    {{-- Сообщения об ошибках/успехе --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Выбор зала и места --}}
    <div class="card mb-4" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
        <div class="card-header" style="background-color: var(--bg-secondary);">
            <h3 class="mb-0" style="color: var(--text-primary);">Выберите место</h3>
        </div>
        <div class="card-body">
            <div id="halls-container">
                {{-- Схема зала --}}
                <div class="hall-container">
                    <h4 class="hall-name mb-4">{{ $hall->hall_name }} ({{ $hall->type_hall }})</h4>
                    
                    <div class="screen">ЭКРАН</div>
                    <div class="seats-grid">
                        @php
                            // Группируем места по рядам
                            $seatsByRow = $hall->seats->groupBy('row_number');
                            // Сортируем ряды
                            $sortedRows = $seatsByRow->keys()->sort(function($a, $b) {
                                return (int)$a - (int)$b;
                            });
                        @endphp
                        
                        @foreach($sortedRows as $rowNum)
                            @php
                                $rowSeats = $seatsByRow[$rowNum]->sortBy('seat_number');
                            @endphp
                            <div class="seat-row">
                                <div class="row-number">{{ $rowNum }}</div>
                                @foreach($rowSeats as $seat)
                                    @php
                                        $isBooked = $seat->is_booked ?? false;
                                    @endphp
                                    <div class="seat {{ $isBooked ? 'seat-booked' : 'seat-available' }}" 
                                         data-seat-id="{{ $seat->id_seat }}" 
                                         data-hall-id="{{ $hall->id_hall }}"
                                         @if(!$isBooked) onclick="selectSeat(this)" @endif>
                                        {{ $seat->seat_number }}
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            {{-- Счетчик выбранных мест --}}
            <div id="selected-seats-info" class="mt-4 mb-3" style="display: none;">
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <span>Выбрано мест: <strong id="selected-count">0</strong> / 7</span>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="clearSelection()">Очистить выбор</button>
                </div>
            </div>

            {{-- Информация о стоимости --}}
            <div id="price-info" class="mt-3 mb-3" style="display: none;">
                <div class="alert alert-warning d-flex justify-content-between align-items-center">
                    <span>Стоимость: <strong id="total-price">0</strong> ₽</span>
                    <small class="text-muted">({{ config('yookassa.ticket_price', 500) }} ₽ за место)</small>
                </div>
            </div>

            {{-- Форма оплаты --}}
            <form id="booking-form" method="POST" action="{{ route('booking.store') }}" style="display: none;">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id_session }}">
                <div id="seat-ids-container"></div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-danger btn-lg w-100" id="submit-btn">
                        <i class="bi bi-credit-card me-1"></i> Перейти к оплате (<span id="submit-count">0</span> билетов)
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Легенда --}}
    <div class="mt-4">
        <div class="d-flex gap-4 justify-content-center">
            <div class="d-flex align-items-center gap-2">
                <div class="seat-legend seat-available"></div>
                <span style="color: var(--text-primary);">Свободно</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="seat-legend seat-selected"></div>
                <span style="color: var(--text-primary);">Выбрано</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="seat-legend seat-booked"></div>
                <span style="color: var(--text-primary);">Забронировано</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedSeatIds = []; // Массив выбранных мест
    const MAX_SEATS = 7;
    
    // Выбор места (множественный выбор до 7 мест)
    window.selectSeat = function(seatElement) {
        const seatId = parseInt(seatElement.dataset.seatId);
        const isSelected = seatElement.classList.contains('seat-selected');
        
        if (isSelected) {
            // Снимаем выделение
            seatElement.classList.remove('seat-selected');
            seatElement.classList.add('seat-available');
            selectedSeatIds = selectedSeatIds.filter(id => id !== seatId);
        } else {
            // Проверяем лимит
            if (selectedSeatIds.length >= MAX_SEATS) {
                alert(`Можно выбрать максимум ${MAX_SEATS} мест`);
                return;
            }
            
            // Добавляем выделение
            seatElement.classList.remove('seat-available');
            seatElement.classList.add('seat-selected');
            selectedSeatIds.push(seatId);
        }
        
        updateBookingForm();
    };
    
    // Обновление формы бронирования
    function updateBookingForm() {
        const count = selectedSeatIds.length;
        const container = document.getElementById('seat-ids-container');
        const form = document.getElementById('booking-form');
        const info = document.getElementById('selected-seats-info');
        const priceInfo = document.getElementById('price-info');
        const countSpan = document.getElementById('selected-count');
        const submitCountSpan = document.getElementById('submit-count');
        const totalPriceSpan = document.getElementById('total-price');
        const submitBtn = document.getElementById('submit-btn');
        
        // Стоимость за место (из конфига или по умолчанию 500)
        const pricePerSeat = {{ config('yookassa.ticket_price', 500) }};
        const totalPrice = count * pricePerSeat;
        
        // Очищаем контейнер
        container.innerHTML = '';
        
        // Добавляем скрытые поля для каждого выбранного места
        selectedSeatIds.forEach(seatId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'seat_ids[]';
            input.value = seatId;
            container.appendChild(input);
        });
        
        // Обновляем счетчики и стоимость
        if (countSpan) countSpan.textContent = count;
        if (submitCountSpan) submitCountSpan.textContent = count;
        if (totalPriceSpan) totalPriceSpan.textContent = totalPrice.toLocaleString('ru-RU');
        
        // Показываем/скрываем форму и информацию
        if (count > 0) {
            form.style.display = 'block';
            info.style.display = 'block';
            priceInfo.style.display = 'block';
            submitBtn.disabled = false;
        } else {
            form.style.display = 'none';
            info.style.display = 'none';
            priceInfo.style.display = 'none';
            submitBtn.disabled = true;
        }
        
        // Прокручиваем к форме при выборе
        if (count > 0) {
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
    
    // Очистка выбора
    window.clearSelection = function() {
        selectedSeatIds = [];
        document.querySelectorAll('.seat-selected').forEach(seat => {
            if (!seat.classList.contains('seat-booked')) {
                seat.classList.remove('seat-selected');
                seat.classList.add('seat-available');
            }
        });
        updateBookingForm();
    };
});
</script>
@endsection
