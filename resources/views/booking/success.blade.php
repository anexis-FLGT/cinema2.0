@extends('layouts.app')

@section('title', 'Бронирование успешно')

@section('content')
<div class="container my-5 text-white">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-dark border-success">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h2 class="text-success mb-4">Бронирование успешно!</h2>
                    
                    @if(isset($allBookings) && $allBookings->count() > 1)
                        <div class="alert alert-info mb-4">
                            <strong>Забронировано билетов: {{ $allBookings->count() }}</strong>
                        </div>
                    @endif
                    
                    <div class="booking-details bg-secondary rounded p-4 mb-4 text-start">
                        <h4 class="mb-4">{{ $booking->session->movie->movie_title ?? 'Фильм не найден' }}</h4>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Дата и время:</strong></div>
                            <div class="col-sm-8">
                                {{ \Carbon\Carbon::parse($booking->show_date)->locale('ru')->isoFormat('D MMMM YYYY') }}, 
                                {{ \Carbon\Carbon::parse($booking->show_time)->format('H:i') }}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Зал:</strong></div>
                            <div class="col-sm-8">{{ $booking->hall->hall_name }}</div>
                        </div>
                        
                        @if(isset($allBookings) && $allBookings->count() > 1)
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Забронированные места:</strong></div>
                                <div class="col-sm-8">
                                    @foreach($allBookings as $b)
                                        <div>Ряд {{ $b->seat->row_number }}, Место {{ $b->seat->seat_number }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Место:</strong></div>
                                <div class="col-sm-8">Ряд {{ $booking->seat->row_number }}, Место {{ $booking->seat->seat_number }}</div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('home') }}" class="btn btn-outline-light">
                            <i class="bi bi-house"></i> На главную
                        </a>
                        @auth
                            <a href="{{ route('user.dashboard') }}" class="btn btn-danger">
                                <i class="bi bi-person"></i> Личный кабинет
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


