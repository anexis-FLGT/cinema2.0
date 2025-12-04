@extends('layouts.app')

@section('title', 'Бронирование билетов - ' . $movie->movie_title)

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/booking.css') }}">

<div class="container my-5" style="color: var(--text-primary);">
    {{-- Информация о фильме --}}
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
            <p class="mb-2"><strong>Продюсер:</strong> {{ $movie->producer }}</p>
        </div>
    </div>

    {{-- Сообщения об ошибках/успехе --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Выбор сеанса --}}
    <div class="card mb-4" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
        <div class="card-header" style="background-color: var(--bg-secondary);">
            <h3 class="mb-0" style="color: var(--text-primary);">Выберите сеанс</h3>
        </div>
        <div class="card-body">
            @if($sessions->count() > 0)
                <div class="row g-3">
                    @foreach($sessionsByDate as $date => $dateSessions)
                        <div class="col-12">
                            <h5 class="mb-3" style="color: var(--text-primary);">{{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('D MMMM YYYY, dddd') }}</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($dateSessions as $session)
                                    <button type="button" 
                                            class="btn btn-outline-light session-btn" 
                                            data-session-id="{{ $session->id_session }}"
                                            data-session-time="{{ $session->date_time_session->format('H:i') }}">
                                        {{ $session->date_time_session->format('H:i') }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: var(--text-secondary);">На данный момент нет доступных сеансов для этого фильма.</p>
            @endif
        </div>
    </div>

    {{-- Выбор зала и места --}}
    <div id="hall-seats-section" class="card" style="display: none; background-color: var(--bg-card); border-color: var(--border-color) !important;">
        <div class="card-header" style="background-color: var(--bg-secondary);">
            <h3 class="mb-0" style="color: var(--text-primary);">Выберите место</h3>
        </div>
        <div class="card-body">
            <div id="halls-container"></div>
            
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
                <input type="hidden" name="session_id" id="selected_session_id">
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

<style>
   
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedSessionId = null;
    let selectedSeatIds = []; // Массив выбранных мест
    const MAX_SEATS = 7;
    
    // Обработка выбора сеанса
    document.querySelectorAll('.session-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Убираем активный класс со всех кнопок
            document.querySelectorAll('.session-btn').forEach(b => b.classList.remove('active'));
            // Добавляем активный класс к выбранной кнопке
            this.classList.add('active');
            
            selectedSessionId = this.dataset.sessionId;
            loadHallSeats(selectedSessionId);
        });
    });
    
    // Загрузка зала и мест
    function loadHallSeats(sessionId) {
        // Скрываем форму бронирования и сбрасываем выбор
        document.getElementById('booking-form').style.display = 'none';
        document.getElementById('selected-seats-info').style.display = 'none';
        selectedSeatIds = [];
        document.getElementById('halls-container').innerHTML = '<div class="text-center"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Загрузка...</span></div></div>';
        
        fetch('{{ route("booking.getHallSeats") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                session_id: sessionId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сервера');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                document.getElementById('halls-container').innerHTML = '<p class="text-danger">' + data.error + '</p>';
                return;
            }
            displayHall(data.hall);
            document.getElementById('hall-seats-section').style.display = 'block';
            document.getElementById('selected_session_id').value = sessionId;
        })
        .catch(error => {
            console.error('Ошибка:', error);
            document.getElementById('halls-container').innerHTML = '<p class="text-danger">Произошла ошибка при загрузке мест. Пожалуйста, попробуйте еще раз.</p>';
        });
    }
    
    // Отображение зала и мест
    function displayHall(hall) {
        const container = document.getElementById('halls-container');
        container.innerHTML = '';
        
        if (!hall || !hall.seats || hall.seats.length === 0) {
            container.innerHTML = '<p style="color: var(--text-secondary);">Нет доступных мест</p>';
            return;
        }
        
        const hallDiv = document.createElement('div');
        hallDiv.className = 'hall-container';
        
        // Группируем места по рядам
        const seatsByRow = {};
        hall.seats.forEach(seat => {
            if (!seatsByRow[seat.row_number]) {
                seatsByRow[seat.row_number] = [];
            }
            seatsByRow[seat.row_number].push(seat);
        });
        
        // Сортируем ряды
        const sortedRows = Object.keys(seatsByRow).sort((a, b) => parseInt(a) - parseInt(b));
        
        let seatsHTML = '<div class="screen">ЭКРАН</div>';
        seatsHTML += '<div class="seats-grid">';
        
        sortedRows.forEach(rowNum => {
            const seats = seatsByRow[rowNum].sort((a, b) => parseInt(a.seat_number) - parseInt(b.seat_number));
            
            seatsHTML += '<div class="seat-row">';
            seatsHTML += `<div class="row-number">${rowNum}</div>`;
            
            seats.forEach(seat => {
                const isBooked = seat.is_booked || seat.status === 'Забронировано';
                const seatClass = isBooked ? 'seat-booked' : 'seat-available';
                
                seatsHTML += `<div class="seat ${seatClass}" 
                                  data-seat-id="${seat.id_seat}" 
                                  data-hall-id="${hall.id_hall}"
                                  ${isBooked ? '' : 'onclick="selectSeat(this)"'}>
                                  ${seat.seat_number}
                              </div>`;
            });
            
            seatsHTML += '</div>';
        });
        
        seatsHTML += '</div>';
        
        hallDiv.innerHTML = `
            <h4 class="hall-name">${hall.hall_name} (${hall.type_hall})</h4>
            ${seatsHTML}
        `;
        
        container.appendChild(hallDiv);
    }
    
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
        const pricePerSeat = 500;
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

