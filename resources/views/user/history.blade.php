@extends('layouts.app')

@section('title', 'История бронирований')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/history.css') }}">

<div class="user-dashboard">
    <div class="container">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="dashboard-title mb-0">История бронирований</h2>
                <a href="{{ route('user.dashboard') }}" class="btn-netflix-outline">
                    <i class="bi bi-arrow-left me-2"></i>Назад в личный кабинет
                </a>
            </div>

            @if (session('success'))
                <div class="alert-netflix alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert-netflix alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            {{-- Секция с историей бронирований --}}
            <div class="mt-4">
                @if($historyBookings->isEmpty())
                    <div class="alert-netflix alert-info">
                        <i class="bi bi-info-circle me-2"></i>У вас пока нет завершенных бронирований.
                    </div>
                @else
                    {{-- Выпадающий список для истории бронирований --}}
                    <div class="accordion" id="historyBookingsAccordion">
                        @foreach($historyBookingsGrouped as $sessionDateTime => $bookingsGroup)
                            @php
                                $firstBooking = $bookingsGroup->first();
                                $movieTitle = $firstBooking->session->movie->movie_title ?? 'Фильм не найден';
                                $sessionDate = \Carbon\Carbon::parse($sessionDateTime)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm');
                            @endphp
                            <div class="accordion-item mb-2" style="background-color: var(--bg-secondary); border: 1px solid var(--border-secondary); border-radius: 8px;">
                                <h2 class="accordion-header" id="headingHistory{{ $loop->index }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistory{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapseHistory{{ $loop->index }}" style="background-color: var(--bg-secondary); color: var(--text-primary); font-weight: 600;">
                                        <div class="w-100">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                <span style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                                                    Сеанс: {{ $sessionDate }}
                                                </span>
                                                <span class="ms-3" style="font-size: 0.95rem; font-weight: 500; text-transform: none;">
                                                    {{ $movieTitle }}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseHistory{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="headingHistory{{ $loop->index }}" data-bs-parent="#historyBookingsAccordion">
                                    <div class="accordion-body" style="background-color: var(--bg-primary);">
                                        <div class="row g-2">
                                            @foreach($bookingsGroup as $booking)
                                                <div class="col-12 col-md-6 col-lg-4">
                                                    <div class="booking-card">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div style="flex: 1;">
                                                                <h5>{{ $booking->session->movie->movie_title ?? 'Фильм не найден' }}</h5>
                                                                <div class="d-flex flex-wrap gap-3 mb-2">
                                                                    <div class="booking-info">
                                                                        <i class="bi bi-calendar-event me-1"></i>
                                                                        {{ \Carbon\Carbon::parse($booking->session->date_time_session)->locale('ru')->isoFormat('D MMM YYYY') }}
                                                                    </div>
                                                                    <div class="booking-info">
                                                                        <i class="bi bi-clock me-1"></i>
                                                                        {{ \Carbon\Carbon::parse($booking->session->date_time_session)->format('H:i') }}
                                                                    </div>
                                                                    <div class="booking-info">
                                                                        <i class="bi bi-door-open me-1"></i>
                                                                        {{ $booking->hall->hall_name ?? 'Не указан' }}
                                                                    </div>
                                                                    <div class="booking-info">
                                                                        <i class="bi bi-seat me-1"></i>
                                                                        Ряд {{ $booking->seat->row_number ?? '?' }}, Место {{ $booking->seat->seat_number ?? '?' }}
                                                                    </div>
                                                                    @if($booking->payment && $booking->payment->amount)
                                                                        <div class="booking-info">
                                                                            <i class="bi bi-currency-ruble me-1"></i>
                                                                            <strong>{{ number_format($booking->payment->amount, 0, ',', ' ') }} ₽</strong>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="text-end ms-2">
                                                                @if($booking->payment)
                                                                    @if($booking->payment->payment_status === 'оплачено')
                                                                        <span class="badge-netflix badge-success">Завершено</span>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                         </div>
                                                     </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Пагинация для истории --}}
                    @if($historyBookings->hasPages())
                        <div class="mt-4">
                            {{ $historyBookings->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* Стили для стрелочки аккордеона истории в темной теме - белый цвет */
    #historyBookingsAccordion .accordion-button::after {
        filter: brightness(0) invert(1);
    }

    /* Для светлой темы возвращаем стандартный цвет */
    [data-theme="light"] #historyBookingsAccordion .accordion-button::after {
        filter: none;
    }

    /* Изменение подсветки активного аккордеона истории с синего на красный */
    #historyBookingsAccordion .accordion-button:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }
</style>
@endsection

