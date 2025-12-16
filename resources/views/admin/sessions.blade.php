@extends('admin.layouts.admin')

@section('title', 'Управление сеансами')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">

<div class="container-fluid">
    {{-- Сообщения об успехе/ошибках --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 p-4 rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-success"><i class="bi bi-clock-history me-2"></i> Управление сеансами</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                <i class="bi bi-plus-circle me-1"></i> Добавить сеанс
            </button>
        </div>

        {{-- Форма фильтрации --}}
        <form method="GET" action="{{ route('admin.sessions.index') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Фильм</label>
                    <select name="movie_id" class="form-select">
                        <option value="">Все фильмы</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id_movie }}" @if(request('movie_id') == $movie->id_movie) selected @endif>{{ $movie->movie_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Зал</label>
                    <select name="hall_id" class="form-select">
                        <option value="">Все залы</option>
                        @foreach($halls as $hall)
                            <option value="{{ $hall->id_hall }}" @if(request('hall_id') == $hall->id_hall) selected @endif>{{ $hall->hall_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Дата от</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Дата до</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success text-white w-100">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                </div>
                @if(request('movie_id') || request('hall_id') || request('date_from') || request('date_to'))
                    <div class="col-12">
                        <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Сбросить фильтры
                        </a>
                    </div>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-success">
                    <tr>
                        <th>Фильм</th>
                        <th>Дата и время</th>
                        <th>Зал</th>
                        <th class="text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $session->movie->movie_title ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->date_time_session)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
                            <td>{{ $session->hall->hall_name ?? '—' }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editSessionModal{{ $session->id_session }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteSessionModal{{ $session->id_session }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                
                                {{-- Модалка архивации сеанса --}}
                                <div class="modal fade" id="deleteSessionModal{{ $session->id_session }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.sessions.destroy', $session->id_session) }}" method="POST" id="deleteSessionForm{{ $session->id_session }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-warning">Архивировать сеанс?</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Вы уверены, что хотите архивировать сеанс фильма <strong>{{ $session->movie->movie_title ?? '—' }}</strong>?</p>
                                                    <p class="mb-0"><strong>Дата и время:</strong> {{ \Carbon\Carbon::parse($session->date_time_session)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}</p>
                                                    <p class="mb-0"><strong>Зал:</strong> {{ $session->hall->hall_name ?? '—' }}</p>
                                                    @php
                                                        $isPastSession = \Carbon\Carbon::parse($session->date_time_session) < now();
                                                        $activeBookingsCount = 0;
                                                        
                                                        if (!$isPastSession) {
                                                            $activeBookingsCount = \App\Models\Booking::where('session_id', $session->id_session)
                                                                ->where(function($query) {
                                                                    $query->whereHas('payment', function($q) {
                                                                        $q->where('payment_status', '!=', 'отменено');
                                                                    })
                                                                    ->orWhereDoesntHave('payment');
                                                                })
                                                                ->count();
                                                        }
                                                    @endphp
                                                    @if($isPastSession)
                                                        <div class="alert alert-info mt-3">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            <strong>Прошедший сеанс.</strong> Бронирования будут сохранены в истории пользователей.
                                                        </div>
                                                    @elseif($activeBookingsCount > 0)
                                                        <div class="alert alert-danger mt-3">
                                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                                            <strong>Невозможно архивировать сеанс!</strong>
                                                            <br>На данный сеанс есть <strong>{{ $activeBookingsCount }}</strong> {{ $activeBookingsCount == 1 ? 'активное бронирование' : ($activeBookingsCount < 5 ? 'активных бронирования' : 'активных бронирований') }}.
                                                        </div>
                                                    @else
                                                        <div class="alert alert-warning mt-3">
                                                            <i class="bi bi-exclamation-circle me-2"></i>
                                                            <strong>Внимание!</strong> Сеанс будет скрыт из списка, но бронирования останутся в истории пользователей.
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    @if($isPastSession)
                                                        {{-- Для прошедших сеансов всегда можно архивировать --}}
                                                        <button type="submit" class="btn btn-warning">Архивировать</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                    @elseif($activeBookingsCount > 0)
                                                        {{-- Для будущих сеансов с активными бронированиями нельзя архивировать --}}
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                    @else
                                                        {{-- Для будущих сеансов без активных бронирований можно архивировать --}}
                                                        <button type="submit" class="btn btn-warning">Архивировать</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                    @endif
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        {{-- Модалка редактирования --}}
                        <div class="modal fade" id="editSessionModal{{ $session->id_session }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.sessions.update', $session->id_session) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold text-success">Редактировать сеанс</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Фильм</label>
                                                <select name="movie_id" class="form-select" required>
                                                    @foreach($movies as $movie)
                                                        <option value="{{ $movie->id_movie }}" {{ $session->movie_id == $movie->id_movie ? 'selected' : '' }}>
                                                            {{ $movie->movie_title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Зал</label>
                                                <select name="hall_id" class="form-select" required>
                                                    @foreach($halls as $hall)
                                                        <option value="{{ $hall->id_hall }}" {{ $session->hall_id == $hall->id_hall ? 'selected' : '' }}>
                                                            {{ $hall->hall_name }} ({{ $hall->type_hall }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Дата и время</label>
                                                <input type="datetime-local" name="date_time_session" class="form-control session-datetime-input" 
                                                       value="{{ \Carbon\Carbon::parse($session->date_time_session)->format('Y-m-d\TH:i') }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Сохранить</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Сеансы пока не добавлены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($sessions->hasPages())
            <div class="mt-4">
                {{ $sessions->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

{{-- Модалка добавления сеанса --}}
<div class="modal fade" id="addSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.sessions.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-success">Добавить сеанс</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Фильм</label>
                        <select name="movie_id" class="form-select" required>
                            <option value="">Выберите фильм</option>
                            @foreach($movies as $movie)
                                <option value="{{ $movie->id_movie }}" {{ old('movie_id') == $movie->id_movie ? 'selected' : '' }}>
                                    {{ $movie->movie_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Зал</label>
                        <select name="hall_id" class="form-select" required>
                            <option value="">Выберите зал</option>
                            @foreach($halls as $hall)
                                <option value="{{ $hall->id_hall }}" {{ old('hall_id') == $hall->id_hall ? 'selected' : '' }}>
                                    {{ $hall->hall_name }} ({{ $hall->type_hall }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Дата и время</label>
                        <input type="datetime-local" name="date_time_session" class="form-control session-datetime-input" 
                               value="{{ old('date_time_session') }}" 
                               min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Добавить</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('error') && old('movie_id'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('addSessionModal'));
        modal.show();
    });
</script>
@endif

<style>
    /* Стили для темной темы в модалках сеансов */
    [data-theme="dark"] #addSessionModal .btn-close,
    [data-theme="dark"] .modal[id^="editSessionModal"] .btn-close,
    [data-theme="dark"] .modal[id^="deleteSessionModal"] .btn-close {
        filter: brightness(0) invert(1);
    }
    
    /* Белый цвет кнопки календаря справа в темной теме */
    [data-theme="dark"] .session-datetime-input::-webkit-calendar-picker-indicator {
        filter: brightness(0) invert(1);
        cursor: pointer;
    }
    
    [data-theme="dark"] .session-datetime-input::-webkit-datetime-edit {
        color: #ffffff;
    }
    
    /* Стили для фильтрации - адаптация под темы */
    [data-theme="dark"] input[type="date"] {
        background-color: var(--input-bg) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }
</style>
@endsection
