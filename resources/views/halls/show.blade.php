@extends('layouts.app')

@section('title', $hall->hall_name . ' — MaxTicket')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/booking.css') }}">
<style>
    /* Убираем анимацию поднятия у карточки со схемой зала */
    .card:hover {
        transform: none !important;
    }
</style>

<div class="container my-5" style="color: var(--text-primary);">
    {{-- Информация о зале --}}
    <div class="row mb-5">
        <div class="col-md-4">
            @if($hall->hall_photo)
                <img src="{{ asset($hall->hall_photo) }}" alt="{{ $hall->hall_name }}" class="img-fluid rounded shadow-lg">
            @else
                <div class="bg-secondary rounded shadow-lg d-flex align-items-center justify-content-center" style="height: 400px;">
                    <span class="text-muted">Фото не загружено</span>
                </div>
            @endif
        </div>
        <div class="col-md-8">
            <h1 class="mb-3">{{ $hall->hall_name }}</h1>
            
            <div class="mb-3">
                <p class="mb-2"><strong><i class="bi bi-film me-2"></i>Тип зала:</strong> {{ $hall->type_hall }}</p>
                <p class="mb-2"><strong><i class="bi bi-people-fill me-2"></i>Количество мест:</strong> {{ $hall->quantity_seats }}</p>
            </div>
            
            @if($hall->description_hall)
                <div class="mt-4 p-3 rounded" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
                    <h5 class="mb-3" style="color: var(--text-primary);">Описание</h5>
                    <p class="mb-0" style="color: var(--text-primary);">{{ $hall->description_hall }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Схема зала --}}
    <div class="card mb-4" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
        <div class="card-header" style="background-color: var(--bg-secondary);">
            <h3 class="mb-0" style="color: var(--text-primary);">Схема зала</h3>
        </div>
        <div class="card-body">
            <div class="hall-container">
                <h4 class="hall-name mb-4 text-center">{{ $hall->hall_name }} ({{ $hall->type_hall }})</h4>
                
                <div class="screen">ЭКРАН</div>
                <div class="seats-grid">
                    @foreach($sortedRows as $rowNum)
                        @php
                            $rowSeats = $seatsByRow[$rowNum]->sortBy('seat_number');
                        @endphp
                        <div class="seat-row">
                            <div class="row-number">{{ $rowNum }}</div>
                            @foreach($rowSeats as $seat)
                                <div class="seat seat-available" style="cursor: default;">
                                    {{ $seat->seat_number }}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Статистика --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--text-primary);">Всего мест</h5>
                    <h3 class="text-primary">{{ $hall->quantity_seats }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--text-primary);">Рядов</h5>
                    <h3 class="text-success">{{ $sortedRows->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--text-primary);">Мест в ряду</h5>
                    <h3 class="text-warning">{{ $hall->quantity_seats > 0 ? round($hall->quantity_seats / $sortedRows->count(), 1) : 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Кнопка назад --}}
    <div class="text-center mb-4">
        <a href="{{ route('halls') }}" class="btn btn-outline-danger">
            <i class="bi bi-arrow-left me-2"></i>Вернуться к списку залов
        </a>
    </div>
</div>
@endsection

