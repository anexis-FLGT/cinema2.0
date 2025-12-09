@extends('admin.layouts.admin')

@section('title', 'Отчет по посещаемости')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2>
            <i class="bi bi-people me-2"></i>Отчет по посещаемости
        </h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.reports.attendance', ['period' => 'today']) }}" 
                   class="btn btn-sm {{ $period == 'today' ? 'btn-primary' : 'btn-outline-primary' }}">Сегодня</a>
                <a href="{{ route('admin.reports.attendance', ['period' => 'week']) }}" 
                   class="btn btn-sm {{ $period == 'week' ? 'btn-primary' : 'btn-outline-primary' }}">Неделя</a>
                <a href="{{ route('admin.reports.attendance', ['period' => 'month']) }}" 
                   class="btn btn-sm {{ $period == 'month' ? 'btn-primary' : 'btn-outline-primary' }}">Месяц</a>
                <a href="{{ route('admin.reports.attendance', ['period' => 'year']) }}" 
                   class="btn btn-sm {{ $period == 'year' ? 'btn-primary' : 'btn-outline-primary' }}">Год</a>
            </div>
            <form method="GET" action="{{ route('admin.reports.attendance') }}" class="d-flex align-items-center gap-2" id="dateFilterForm">
                <input type="date" name="date_from" class="form-control form-control-sm" 
                       value="{{ $dateFrom }}" style="width: 150px;" id="dateFrom">
                <span class="text-muted">—</span>
                <input type="date" name="date_to" class="form-control form-control-sm" 
                       value="{{ $dateTo }}" style="width: 150px;" id="dateTo">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Общая статистика -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="mb-3">Общая статистика</h4>
            <div class="row">
                <div class="col-md-6">
                    <h2 class="text-primary mb-0">{{ $totalTickets }}</h2>
                    <small class="text-muted">Проданных билетов</small>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Период: {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Заполняемость залов -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Заполняемость залов</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Фильм</th>
                                    <th>Зал</th>
                                    <th class="text-end">Заполняемость</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hallOccupancy as $session)
                                    <tr>
                                        <td>{{ $session['movie_title'] }}</td>
                                        <td>{{ $session['hall_name'] }}</td>
                                        <td class="text-end">
                                            <span class="badge {{ $session['occupancy'] >= 70 ? 'bg-success' : ($session['occupancy'] >= 40 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ $session['occupancy'] }}%
                                            </span>
                                            <small class="text-muted ms-2">({{ $session['booked_seats'] }}/{{ $session['total_seats'] }})</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Нет данных</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Популярные сеансы -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Популярные сеансы</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Фильм</th>
                                    <th>Дата и время</th>
                                    <th class="text-end">Билетов</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($popularSessions as $session)
                                    <tr>
                                        <td>{{ $session->movie_title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($session->date_time_session)->format('d.m.Y H:i') }}</td>
                                        <td class="text-end">{{ $session->tickets_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Нет данных</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к отчетам
        </a>
        <a href="{{ route('admin.reports.attendance.pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-pdf me-2"></i>Экспорт в PDF
        </a>
    </div>
</div>

<style>
    /* Стили для полей ввода дат в темной теме */
    [data-theme="dark"] input[type="date"] {
        background-color: var(--bg-secondary) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    
    [data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator {
        filter: brightness(0) invert(1);
        cursor: pointer;
    }
</style>
@endsection

