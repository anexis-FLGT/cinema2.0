@extends('admin.layouts.admin')

@section('title', 'Панель администратора')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

<div class="container-fluid">
    <div class="row g-4 mb-4">
        {{-- Карточки статистики --}}
        <div class="col-md-3 col-lg-2">
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

        <div class="col-md-3 col-lg-2">
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

        <div class="col-md-3 col-lg-2">
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

        <div class="col-md-3 col-lg-3">
            <div class="stat-card p-3" style="border-left: 4px solid #28a745;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Активные бронирования</small>
                        <h3 class="mb-0">{{ $activeBookingsCount ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-ticket-perforated stat-icon" style="color: #28a745;"></i>
                </div>
                <p class="mb-0 text-muted mt-2">Оплаченные</p>
            </div>
        </div>

        <div class="col-md-3 col-lg-3">
            <div class="stat-card p-3" style="border-left: 4px solid #dc3545;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Отмененные бронирования</small>
                        <h3 class="mb-0">{{ $cancelledBookingsCount ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-x-circle stat-icon" style="color: #dc3545;"></i>
                </div>
                <p class="mb-0 text-muted mt-2">Отмененные</p>
            </div>
        </div>
    </div>

    {{-- Статистика выручки --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 p-4 rounded-4">
                <h4 class="fw-bold text-success mb-3">
                    <i class="bi bi-cash-coin me-2"></i>Статистика выручки
                </h4>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="stat-card p-3" style="border-left: 4px solid #6f42c1;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted">Общая выручка</small>
                                    <h3 class="mb-0">{{ number_format($totalRevenue ?? 0, 2, ',', ' ') }} ₽</h3>
                                </div>
                                <i class="bi bi-cash-stack stat-icon" style="color: #6f42c1; font-size: 2rem;"></i>
                            </div>
                            <p class="mb-0 text-muted mt-2">За все время</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card p-3" style="border-left: 4px solid #28a745;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted">Выручка за сегодня</small>
                                    <h3 class="mb-0">{{ number_format($revenueToday ?? 0, 2, ',', ' ') }} ₽</h3>
                                </div>
                                <i class="bi bi-calendar-day stat-icon" style="color: #28a745; font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card p-3" style="border-left: 4px solid #17a2b8;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted">Выручка за неделю</small>
                                    <h3 class="mb-0">{{ number_format($revenueWeek ?? 0, 2, ',', ' ') }} ₽</h3>
                                </div>
                                <i class="bi bi-calendar-week stat-icon" style="color: #17a2b8; font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card p-3" style="border-left: 4px solid #ffc107;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-muted">Выручка за месяц</small>
                                    <h3 class="mb-0">{{ number_format($revenueMonth ?? 0, 2, ',', ' ') }} ₽</h3>
                                </div>
                                <i class="bi bi-calendar-month stat-icon" style="color: #ffc107; font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
