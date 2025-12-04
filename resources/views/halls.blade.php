@extends('layouts.app')

@section('title', 'Наши залы — MaxTicket')

@section('content')
<div class="container py-5">
    <h1 class="text-center mb-5 fw-bold" style="color: var(--text-primary);">Наши залы</h1>

    <div class="row g-4">
        @forelse ($halls as $hall)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm" style="background-color: var(--bg-card); color: var(--text-primary); border-color: var(--border-color) !important;">
                    <img src="{{ asset($hall->hall_photo) }}" class="card-img-top" alt="{{ $hall->hall_name }}">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-danger">{{ $hall->hall_name }}</h5>
                        <p class="mb-1" style="color: var(--text-primary);"><i class="bi bi-people-fill"></i> Мест: {{ $hall->quantity_seats }}</p>
                        <p class="mb-1" style="color: var(--text-primary);"><i class="bi bi-film"></i> Тип: {{ $hall->type_hall }}</p>
                        <p class="card-text mt-3 flex-grow-1">{{ $hall->description_hall }}</p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-secondary">Информация о залах пока недоступна.</p>
        @endforelse
    </div>
</div>
@endsection
