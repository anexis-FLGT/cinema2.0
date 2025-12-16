@extends('admin.layouts.admin')

@section('title', 'Отчет по фильмам')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2>
            <i class="bi bi-film me-2"></i>Отчет по фильмам
        </h2>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.reports.movies', ['period' => 'today']) }}" 
                   class="btn btn-sm {{ $period == 'today' ? 'btn-warning' : 'btn-outline-warning' }}">Сегодня</a>
                <a href="{{ route('admin.reports.movies', ['period' => 'week']) }}" 
                   class="btn btn-sm {{ $period == 'week' ? 'btn-warning' : 'btn-outline-warning' }}">Неделя</a>
                <a href="{{ route('admin.reports.movies', ['period' => 'month']) }}" 
                   class="btn btn-sm {{ $period == 'month' ? 'btn-warning' : 'btn-outline-warning' }}">Месяц</a>
                <a href="{{ route('admin.reports.movies', ['period' => 'year']) }}" 
                   class="btn btn-sm {{ $period == 'year' ? 'btn-warning' : 'btn-outline-warning' }}">Год</a>
            </div>
            <form method="GET" action="{{ route('admin.reports.movies') }}" class="d-flex align-items-center gap-2" id="dateFilterForm">
                <input type="date" name="date_from" class="form-control form-control-sm" 
                       value="{{ $dateFrom }}" style="width: 150px;" id="dateFrom">
                <span class="text-muted">—</span>
                <input type="date" name="date_to" class="form-control form-control-sm" 
                       value="{{ $dateTo }}" style="width: 150px;" id="dateTo">
                <button type="submit" class="btn btn-sm btn-warning">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Популярные фильмы -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-star me-2"></i>Самые популярные фильмы</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Фильм</th>
                                    <th class="text-end">Билетов</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($popularMovies as $movie)
                                    <tr>
                                        <td>{{ ($popularMovies->currentPage() - 1) * $popularMovies->perPage() + $loop->iteration }}</td>
                                        <td>{{ $movie->movie_title }}</td>
                                        <td class="text-end">{{ $movie->tickets_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Нет данных</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($popularMovies->hasPages())
                        <div class="card-footer">
                            {{ $popularMovies->appends(request()->except('popular_page'))->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Прибыльные фильмы -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Самые прибыльные фильмы</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Фильм</th>
                                    <th class="text-end">Выручка</th>
                                    <th class="text-end">Билетов</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($profitableMovies as $movie)
                                    <tr>
                                        <td>{{ ($profitableMovies->currentPage() - 1) * $profitableMovies->perPage() + $loop->iteration }}</td>
                                        <td>{{ $movie->movie_title }}</td>
                                        <td class="text-end">{{ number_format($movie->revenue, 2, ',', ' ') }} ₽</td>
                                        <td class="text-end">{{ $movie->tickets_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Нет данных</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($profitableMovies->hasPages())
                        <div class="card-footer">
                            {{ $profitableMovies->appends(request()->except('profitable_page'))->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Средняя заполняемость -->
    @if($movieOccupancy->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Средняя заполняемость по фильмам</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Фильм</th>
                            <th class="text-end">Сеансов</th>
                            <th class="text-end">Средняя заполняемость</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movieOccupancy as $movie)
                            <tr>
                                <td>{{ $movie['movie_title'] }}</td>
                                <td class="text-end">{{ $movie['sessions_count'] }}</td>
                                <td class="text-end">
                                    <span class="badge {{ $movie['avg_occupancy'] >= 70 ? 'bg-success' : ($movie['avg_occupancy'] >= 40 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $movie['avg_occupancy'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($movieOccupancy->hasPages())
                <div class="card-footer">
                    {{ $movieOccupancy->appends(request()->except('occupancy_page'))->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к отчетам
        </a>
        <a href="{{ route('admin.reports.movies.pdf', request()->all()) }}" class="btn btn-danger" target="_blank">
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

