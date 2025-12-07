@extends('admin.layouts.admin')

@section('title', 'Панель администратора')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

<div class="container-fluid">
    <div class="row g-4 mb-4">
        {{-- Карточки статистики --}}
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Фильмы</small>
                        <h3 class="mb-0">{{ $moviesCount ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-film stat-icon"></i>
                </div>
                <p class="mb-0 text-muted mt-2">Всего фильмов</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Сеансы</small>
                        <h3 class="mb-0">{{ $sessionsCount ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-clock-history stat-icon"></i>
                </div>
                <p class="mb-0 text-muted mt-2">Всего сеансов</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Пользователи</small>
                        <h3 class="mb-0">{{ $usersCount ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-people stat-icon"></i>
                </div>
                <p class="mb-0 text-muted mt-2">Всего пользователей</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Бронирования</small>
                        <h3 class="mb-0">{{ $bookingsCount ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-ticket-perforated stat-icon"></i>
                </div>
                <p class="mb-0 text-muted mt-2">Всего бронирований</p>
            </div>
        </div>
    </div>






</div>

@endsection
