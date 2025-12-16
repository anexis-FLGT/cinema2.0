@extends('admin.layouts.admin')

@section('title', 'Отчет по выручке')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2>
            <i class="bi bi-cash-coin me-2"></i>Отчет по выручке
        </h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.reports.revenue', ['period' => 'today']) }}" 
                   class="btn btn-sm {{ $period == 'today' ? 'btn-success' : 'btn-outline-success' }}">Сегодня</a>
                <a href="{{ route('admin.reports.revenue', ['period' => 'week']) }}" 
                   class="btn btn-sm {{ $period == 'week' ? 'btn-success' : 'btn-outline-success' }}">Неделя</a>
                <a href="{{ route('admin.reports.revenue', ['period' => 'month']) }}" 
                   class="btn btn-sm {{ $period == 'month' ? 'btn-success' : 'btn-outline-success' }}">Месяц</a>
                <a href="{{ route('admin.reports.revenue', ['period' => 'year']) }}" 
                   class="btn btn-sm {{ $period == 'year' ? 'btn-success' : 'btn-outline-success' }}">Год</a>
            </div>
            <form method="GET" action="{{ route('admin.reports.revenue') }}" class="d-flex align-items-center gap-2" id="dateFilterForm">
                <input type="date" name="date_from" class="form-control form-control-sm" 
                       value="{{ $dateFrom }}" style="width: 150px;" id="dateFrom">
                <span class="text-muted">—</span>
                <input type="date" name="date_to" class="form-control form-control-sm" 
                       value="{{ $dateTo }}" style="width: 150px;" id="dateTo">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Общая выручка -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="mb-3">Общая выручка за {{ $period == 'today' ? 'сегодня' : ($period == 'week' ? 'неделю' : ($period == 'month' ? 'месяц' : 'год')) }}</h4>
            <h2 class="text-success mb-0">{{ number_format($totalRevenue, 2, ',', ' ') }} ₽</h2>
            <small class="text-muted">Период: {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}</small>
        </div>
    </div>

    <div class="row g-4">
        <!-- Выручка по фильмам -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-film me-2"></i>Выручка по фильмам</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Фильм</th>
                                    <th class="text-end">Выручка</th>
                                    <th class="text-end">Билетов</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueByMovie as $movie)
                                    <tr>
                                        <td>{{ $movie->movie_title }}</td>
                                        <td class="text-end">{{ number_format($movie->revenue, 2, ',', ' ') }} ₽</td>
                                        <td class="text-end">{{ $movie->bookings_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Нет данных</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($revenueByMovie->hasPages())
                        <div class="card-footer">
                            {{ $revenueByMovie->appends(request()->except('movies_page'))->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Выручка по залам -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Выручка по залам</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Зал</th>
                                    <th class="text-end">Выручка</th>
                                    <th class="text-end">Билетов</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueByHall as $hall)
                                    <tr>
                                        <td>{{ $hall->hall_name }}</td>
                                        <td class="text-end">{{ number_format($hall->revenue, 2, ',', ' ') }} ₽</td>
                                        <td class="text-end">{{ $hall->bookings_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Нет данных</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($revenueByHall->hasPages())
                        <div class="card-footer">
                            {{ $revenueByHall->appends(request()->except('halls_page'))->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Динамика выручки -->
    @if($dailyRevenue->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Динамика выручки</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th class="text-end">Выручка</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyRevenue as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->date)->format('d.m.Y') }}</td>
                                <td class="text-end">{{ number_format($day->revenue, 2, ',', ' ') }} ₽</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($dailyRevenue->hasPages())
                <div class="card-footer">
                    {{ $dailyRevenue->appends(request()->except('daily_page'))->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к отчетам
        </a>
        <a href="{{ route('admin.reports.revenue.pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
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

