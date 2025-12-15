@extends('layouts.app')

@section('title', 'Наши залы — MaxTicket')

@section('content')
<style>
    /* Стили для карточек залов с hover эффектом */
    .hall-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    
    .hall-card-link .card {
        transition: all 0.3s ease;
    }
    
    .hall-card-link:hover .card {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(229, 9, 20, 0.3) !important;
        border-color: var(--accent-primary) !important;
    }
    
    .hall-card-link:hover .card-title {
        color: var(--accent-primary) !important;
    }
</style>

<div class="container py-5">
    <h1 class="text-center mb-5 fw-bold" style="color: var(--text-primary);">Наши залы</h1>

    <div class="row g-4">
        @forelse ($halls as $hall)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('halls.show', $hall->id_hall) }}" class="hall-card-link">
                    <div class="card h-100 shadow-sm" style="background-color: var(--bg-card); color: var(--text-primary); border-color: var(--border-color) !important; cursor: pointer;">
                        <img src="{{ asset($hall->hall_photo) }}" class="card-img-top" alt="{{ $hall->hall_name }}">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-danger">{{ $hall->hall_name }}</h5>
                            <p class="mb-1" style="color: var(--text-primary);"><i class="bi bi-people-fill"></i> Мест: {{ $hall->quantity_seats }}</p>
                            <p class="mb-1" style="color: var(--text-primary);"><i class="bi bi-film"></i> Тип: {{ $hall->type_hall }}</p>
                            <p class="card-text mt-3 flex-grow-1">{{ $hall->description_hall }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <p class="text-center text-secondary">Информация о залах пока недоступна.</p>
        @endforelse
    </div>
</div>
@endsection
