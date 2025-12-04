@extends('layouts.app')

@section('title', 'Расписание сеансов — MaxTicket')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/session.css') }}">    

<div class="container py-5">
    <h1 class="text-center mb-5 fw-bold" style="color: var(--text-primary);">
        <i class="bi bi-clock-history me-2"></i>Расписание сеансов
    </h1>

    @if($movies->isEmpty())
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>На данный момент нет доступных сеансов.
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
                                    <span class="badge bg-secondary me-2">
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
                                            <a href="{{ route('booking.show', $movie->id_movie) }}" 
                                               class="btn btn-outline-light session-time-btn"
                                               data-session-id="{{ $session->id_session }}"
                                               title="Зал: {{ $session->hall->hall_name ?? 'Не указан' }}">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $session->date_time_session->format('H:i') }}
                                                @if($session->hall)
                                                    <small class="d-block mt-1" style="color: var(--text-primary);">{{ $session->hall->hall_name }}</small>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-3">
                                <a href="{{ route('movie.show', $movie->id_movie) }}" class="btn btn-outline-light me-2">
                                    <i class="bi bi-info-circle me-1"></i>Подробнее о фильме
                                </a>
                                <a href="{{ route('booking.show', $movie->id_movie) }}" class="btn btn-danger">
                                    <i class="bi bi-ticket-perforated me-1"></i>Забронировать билет
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

@endsection




