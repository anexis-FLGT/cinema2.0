@extends('layouts.app')

@section('title', 'Расписание сеансов — MaxTicket')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/session.css') }}">    

<div class="container py-5">
    <h1 class="text-center mb-5 fw-bold" style="color: var(--text-primary);">
        <i class="bi bi-clock-history me-2"></i>Расписание сеансов
    </h1>

    {{-- Фильтр по дате --}}
    @if(isset($availableDates) && $availableDates->isNotEmpty())
        <div class="card mb-4 date-filter-card" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
            <div class="card-body">
                <form method="GET" action="{{ route('sessions') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="date-filter" class="form-label" style="color: var(--text-primary);">
                            <i class="bi bi-calendar-event me-1"></i>Фильтр по дате
                        </label>
                        <select name="date" id="date-filter" class="form-select date-filter-select" onchange="this.form.submit()" style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                            <option value="">Все даты</option>
                            @foreach($availableDates as $dateInfo)
                                <option value="{{ $dateInfo['value'] }}" {{ $selectedDate == $dateInfo['value'] ? 'selected' : '' }}>
                                    {{ $dateInfo['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        @if($selectedDate)
                            <a href="{{ route('sessions') }}" class="btn btn-outline-secondary w-100" style="border-color: var(--border-color); color: var(--text-primary);">
                                <i class="bi bi-x-circle me-1"></i>Сбросить
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($movies->isEmpty())
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            @if($selectedDate)
                На выбранную дату нет доступных сеансов.
            @else
                На данный момент нет доступных сеансов.
            @endif
        </div>
    @else
        {{-- Сеансы по фильмам --}}
        @foreach($movies as $movie)
            <div class="card mb-4 shadow-sm" style="background-color: var(--bg-card); color: var(--text-primary); border-color: var(--border-color) !important;" id="movie-{{ $movie->id_movie }}">
                <div class="row g-0">
                    <div class="col-md-3">
                        <img src="{{ $movie->poster }}" alt="{{ $movie->movie_title }}" 
                             class="img-fluid rounded-start" style="height: 100%; object-fit: cover;">
                    </div>
                    <div class="col-md-9">
                        <div class="card-body">
                            <h4 class="card-title fw-bold mb-3" style="color: var(--text-primary);">{{ $movie->movie_title }}</h4>
                            
                            <div class="mb-3">
                                @if($movie->genres && $movie->genres->count() > 0)
                                    <span class="badge genre-badge me-2">
                                        {{ $movie->genres->pluck('genre_name')->join(', ') }}
                                    </span>
                                @endif
                                <span class="badge bg-warning text-dark me-2">{{ $movie->age_limit }}</span>
                                <span style="color: var(--text-primary);"><i class="bi bi-clock me-1"></i>{{ $movie->duration }}</span>
                            </div>

                            <p class="card-text mb-3" style="color: var(--text-primary);">{{ Str::limit($movie->description, 300) }}</p>

                            {{-- Сеансы для этого фильма, сгруппированные по датам --}}
                            @php
                                $movieSessions = $movie->sessions->groupBy(function($session) {
                                    return $session->date_time_session->format('Y-m-d');
                                });
                            @endphp

                            @foreach($movieSessions as $date => $dateSessions)
                                <div class="mb-3" id="date-{{ $date }}">
                                    <h6 class="mb-2" style="color: var(--text-primary);">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('D MMMM YYYY, dddd') }}
                                    </h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($dateSessions as $session)
                                            @auth
                                                <a href="{{ route('booking.show', $session->id_session) }}" 
                                                   class="btn btn-outline-light session-time-btn"
                                                   title="Зал: {{ $session->hall->hall_name ?? 'Не указан' }}">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $session->date_time_session->format('H:i') }}
                                                    @if($session->hall)
                                                        <small class="d-block mt-1" style="color: var(--text-primary);">{{ $session->hall->hall_name }}</small>
                                                    @endif
                                                </a>
                                            @else
                                                <button type="button" 
                                                        class="btn btn-outline-light session-time-btn"
                                                        title="Для бронирования необходимо войти в систему"
                                                        onclick="window.location.href='{{ route('login') }}'"
                                                        style="opacity: 0.6; cursor: pointer;">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $session->date_time_session->format('H:i') }}
                                                    @if($session->hall)
                                                        <small class="d-block mt-1" style="color: var(--text-primary);">{{ $session->hall->hall_name }}</small>
                                                    @endif
                                                    <small class="d-block mt-1 text-warning">
                                                        <i class="bi bi-lock me-1"></i>Войти
                                                    </small>
                                                </button>
                                            @endauth
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-3">
                                <a href="{{ route('movie.show', $movie->id_movie) }}" class="btn btn-outline-light">
                                    <i class="bi bi-info-circle me-1"></i>Подробнее о фильме
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<style>
    /* Стили для фильтра по дате в темной теме */
    [data-theme="dark"] .date-filter-select {
        background-color: var(--input-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    
    [data-theme="dark"] .date-filter-select option {
        background-color: var(--bg-card) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .date-filter-select:focus {
        background-color: var(--input-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
        box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1) !important;
    }
    
    [data-theme="dark"] .btn-outline-secondary {
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .btn-outline-secondary:hover {
        background-color: var(--bg-secondary) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    
    /* Белая стрелочка в выпадающем списке для темной темы */
    [data-theme="dark"] .date-filter-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        background-size: 16px 12px !important;
    }
    
    /* Убираем эффект поднятия при наведении для блока фильтра */
    .date-filter-card:hover {
        transform: none !important;
        box-shadow: none !important;
    }
    
    /* Адаптация жанра под светлую и темную тему */
    .genre-badge {
        background-color: #6c757d;
        color: #ffffff;
    }
    
    [data-theme="dark"] .genre-badge {
        background-color: var(--bg-secondary) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-color);
    }
    
    [data-theme="light"] .genre-badge {
        background-color: #6c757d !important;
        color: #ffffff !important;
    }
</style>

@endsection




