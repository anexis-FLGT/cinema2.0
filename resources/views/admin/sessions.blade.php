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

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Фильм</th>
                        <th>Дата и время</th>
                        <th>Зал</th>
                        <th class="text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $session->id_session }}</td>
                            <td>{{ $session->movie->movie_title ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->date_time_session)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm') }}</td>
                            <td>{{ $session->hall->hall_name ?? '—' }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editSessionModal{{ $session->id_session }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.sessions.destroy', $session->id_session) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Вы уверены, что хотите удалить этот сеанс?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
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
                                                <input type="datetime-local" name="date_time_session" class="form-control" 
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
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Сеансы пока не добавлены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        <div class="mt-3">
            {{ $sessions->links() }}
        </div>
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
                                <option value="{{ $movie->id_movie }}">{{ $movie->movie_title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Зал</label>
                        <select name="hall_id" class="form-select" required>
                            <option value="">Выберите зал</option>
                            @foreach($halls as $hall)
                                <option value="{{ $hall->id_hall }}">{{ $hall->hall_name }} ({{ $hall->type_hall }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Дата и время</label>
                        <input type="datetime-local" name="date_time_session" class="form-control" required>
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

@endsection
