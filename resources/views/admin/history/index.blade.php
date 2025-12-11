@extends('admin.layouts.admin')

@section('title', 'История операций')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="bi bi-clock-history me-2"></i>История операций
    </h2>

    {{-- Статистика --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Всего бронирований</small>
                        <h3 class="mb-0">{{ $totalBookings ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-ticket-perforated stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Оплачено</small>
                        <h3 class="mb-0">{{ $paidBookings ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-check-circle stat-icon" style="color: #28a745;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Отменено</small>
                        <h3 class="mb-0">{{ $cancelledBookings ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-x-circle stat-icon" style="color: #dc3545;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted">Общая выручка</small>
                        <h3 class="mb-0">{{ number_format($totalRevenue ?? 0, 0, ',', ' ') }} ₽</h3>
                    </div>
                    <i class="bi bi-cash-coin stat-icon" style="color: #ffc107;"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Форма фильтрации --}}
    <div class="card shadow-sm border-0 p-4 rounded-4 mb-4">
        <form method="GET" action="{{ route('admin.history.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Поиск по ФИО пользователя</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Введите фамилию, имя или отчество..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Пользователь</label>
                    <select name="user_id" class="form-select">
                        <option value="">Все пользователи</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id_user }}" @if(request('user_id') == $user->id_user) selected @endif>
                                {{ $user->last_name }} {{ $user->first_name }} {{ $user->middle_name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Статус платежа</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Все статусы</option>
                        <option value="оплачено" @if(request('payment_status') == 'оплачено') selected @endif>Оплачено</option>
                        <option value="ожидание" @if(request('payment_status') == 'ожидание') selected @endif>Ожидание</option>
                        <option value="отменено" @if(request('payment_status') == 'отменено') selected @endif>Отменено</option>
                        <option value="ожидает_подтверждения" @if(request('payment_status') == 'ожидает_подтверждения') selected @endif>Ожидает подтверждения</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Период</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="От">
                        </div>
                        <div class="col-6">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="До">
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-info text-white">
                        <i class="bi bi-funnel me-1"></i> Применить фильтры
                    </button>
                    @if(request('search') || request('user_id') || request('payment_status') || request('date_from') || request('date_to'))
                        <a href="{{ route('admin.history.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Сбросить фильтры
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Таблица истории --}}
    <div class="card shadow-sm border-0 p-4 rounded-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-info">
                    <tr>
                        <th>ID</th>
                        <th>Дата создания</th>
                        <th>Пользователь</th>
                        <th>Фильм</th>
                        <th>Сеанс</th>
                        <th>Зал</th>
                        <th>Место</th>
                        <th>Статус платежа</th>
                        <th>Сумма</th>
                        <th class="text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id_booking }}</td>
                            <td>
                                @if($booking->created_ad)
                                    {{ \Carbon\Carbon::parse($booking->created_ad)->locale('ru')->isoFormat('D MMM YYYY, HH:mm') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($booking->user)
                                    {{ $booking->user->last_name }} {{ $booking->user->first_name }} {{ $booking->user->middle_name ?? '' }}
                                @else
                                    <span class="text-muted">Пользователь удалён</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->session && $booking->session->movie)
                                    {{ $booking->session->movie->movie_title }}
                                @else
                                    <span class="text-muted">Фильм удалён</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->session)
                                    {{ \Carbon\Carbon::parse($booking->session->date_time_session)->locale('ru')->isoFormat('D MMM YYYY, HH:mm') }}
                                @else
                                    <span class="text-muted">Сеанс удалён</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->session && $booking->session->hall)
                                    {{ $booking->session->hall->hall_name }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->seat)
                                    Ряд {{ $booking->seat->row_number }}, Место {{ $booking->seat->seat_number }}
                                @else
                                    <span class="text-muted">Место удалено</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->payment)
                                    @if($booking->payment->payment_status === 'оплачено')
                                        <span class="badge bg-success">Оплачено</span>
                                    @elseif($booking->payment->payment_status === 'ожидание')
                                        <span class="badge bg-warning">Ожидание</span>
                                    @elseif($booking->payment->payment_status === 'отменено')
                                        <span class="badge bg-danger">Отменено</span>
                                    @elseif($booking->payment->payment_status === 'ожидает_подтверждения')
                                        <span class="badge bg-info">Ожидает подтверждения</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $booking->payment->payment_status }}</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Нет платежа</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->payment && $booking->payment->amount)
                                    <strong>{{ number_format($booking->payment->amount, 0, ',', ' ') }} ₽</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.history.show', $booking->id_booking) }}" class="btn btn-sm btn-outline-info" title="Подробнее">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Бронирования не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($bookings->hasPages())
            <div class="mt-4">
                {{ $bookings->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

<style>
    /* Стили для поиска - адаптация под темы */
    [data-theme="dark"] .input-group-text {
        background-color: var(--bg-secondary) !important;
        border-color: var(--border-color) !important;
        color: #ffffff !important;
    }
    
    [data-theme="dark"] .input-group-text i {
        color: #ffffff !important;
    }
    
    [data-theme="dark"] .input-group .form-control {
        background-color: var(--input-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .input-group .form-control::placeholder {
        color: #ffffff !important;
        opacity: 0.7;
    }
    
    [data-theme="dark"] .input-group .form-control:focus {
        background-color: var(--input-bg) !important;
        border-color: var(--input-focus-border) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] input[type="date"] {
        background-color: var(--input-bg) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }
</style>
@endsection

