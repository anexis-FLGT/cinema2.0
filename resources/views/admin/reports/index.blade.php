@extends('admin.layouts.admin')

@section('title', 'Отчёты')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">
        <i class="bi bi-bar-chart-line me-2"></i>Отчёты
    </h2>

    <div class="row g-4">
        <!-- Отчет по выручке -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-cash-coin text-success" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Отчет по выручке</h5>
                            <small class="text-muted">Финансовая статистика</small>
                        </div>
                    </div>
                    <p class="text-muted">Детальная информация о выручке за период, по фильмам и залам</p>
                    <a href="{{ route('admin.reports.revenue') }}" class="btn btn-success w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>Открыть отчет
                    </a>
                </div>
            </div>
        </div>

        <!-- Отчет по посещаемости -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Отчет по посещаемости</h5>
                            <small class="text-muted">Статистика посещений</small>
                        </div>
                    </div>
                    <p class="text-muted">Количество билетов, заполняемость залов, популярные сеансы</p>
                    <a href="{{ route('admin.reports.attendance') }}" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>Открыть отчет
                    </a>
                </div>
            </div>
        </div>

        <!-- Отчет по фильмам -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-film text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Отчет по фильмам</h5>
                            <small class="text-muted">Аналитика фильмов</small>
                        </div>
                    </div>
                    <p class="text-muted">Популярные и прибыльные фильмы, средняя заполняемость</p>
                    <a href="{{ route('admin.reports.movies') }}" class="btn btn-warning w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>Открыть отчет
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

