@extends('admin.layouts.admin')

@section('title', 'Управление залами')

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
            <h4 class="fw-bold text-warning"><i class="bi bi-building me-2"></i> Управление залами</h4>
            <button class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#addHallModal">
                <i class="bi bi-plus-circle me-1"></i> Добавить зал
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-warning">
                    <tr>
                        <th>№</th>
                        <th>Название</th>
                        <th>Тип</th>
                        <th>Количество мест</th>
                        <th>Описание</th>
                        <th class="text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($halls as $hall)
                        <tr>
                            <td>{{ $hall->id_hall }}</td>
                            <td>{{ $hall->hall_name }}</td>
                            <td>{{ $hall->type_hall }}</td>
                            <td>{{ $hall->seats_count ?? 0 }}</td>
                            <td>{{ Str::limit($hall->description_hall, 50) }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editHallModal{{ $hall->id_hall }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteHallModal{{ $hall->id_hall }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                
                                {{-- Модалка удаления зала --}}
                                <div class="modal fade" id="deleteHallModal{{ $hall->id_hall }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.halls.destroy', $hall->id_hall) }}" method="POST" id="deleteHallForm{{ $hall->id_hall }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">Удалить зал?</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Вы уверены, что хотите удалить зал <strong>{{ $hall->hall_name }}</strong>?</p>
                                                    @php
                                                        $sessionsCount = \App\Models\Session::where('hall_id', $hall->id_hall)->count();
                                                    @endphp
                                                    @if($sessionsCount > 0)
                                                        <div class="alert alert-danger mt-3">
                                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                                            <strong>Невозможно удалить зал!</strong>
                                                            <br>На этот зал запланировано <strong>{{ $sessionsCount }}</strong> {{ $sessionsCount == 1 ? 'сеанс' : ($sessionsCount < 5 ? 'сеанса' : 'сеансов') }}.
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    @if($sessionsCount > 0)
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                    @else
                                                        <button type="submit" class="btn btn-danger">Удалить</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                    @endif
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Залы пока не добавлены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($halls->hasPages())
            <div class="mt-4">
                {{ $halls->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

    {{-- Модалка добавления зала --}}
    @include('admin.halls.modals.create')

    {{-- Модалки редактирования --}}
    @foreach ($halls as $hall)
        @include('admin.halls.modals.edit', ['hall' => $hall])
    @endforeach
</div>

<style>
    /* Стили для темной темы в модалках залов */
    [data-theme="dark"] #addHallModal .btn-close,
    [data-theme="dark"] .modal[id^="editHallModal"] .btn-close,
    [data-theme="dark"] .modal[id^="deleteHallModal"] .btn-close {
        filter: brightness(0) invert(1);
    }
</style>

<script>
// Автоматически открываем модальное окно добавления зала, если есть ошибки валидации
@if(($errors->any() || session('error')) && !session('editing_hall_id'))
    document.addEventListener('DOMContentLoaded', function() {
        const addHallModal = new bootstrap.Modal(document.getElementById('addHallModal'));
        addHallModal.show();
    });
@endif

// Автоматически открываем модальное окно редактирования зала, если есть ошибки валидации
@if(session('editing_hall_id'))
    document.addEventListener('DOMContentLoaded', function() {
        const editHallModal = document.getElementById('editHallModal{{ session('editing_hall_id') }}');
        if (editHallModal) {
            const modal = new bootstrap.Modal(editHallModal);
            modal.show();
        }
    });
@endif
</script>

@endsection

