@extends('admin.layouts.admin')

@section('title', 'Управление залами')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-building me-2"></i>Управление залами
        </h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addHallModal">
            <i class="bi bi-plus-circle me-2"></i>Добавить зал
        </button>
    </div>

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

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-success">
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
                            <form action="{{ route('admin.halls.destroy', $hall->id_hall) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Вы уверены, что хотите удалить этот зал?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Залы не найдены</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $halls->links() }}

    {{-- Модалка добавления зала --}}
    @include('admin.halls.modals.create')

    {{-- Модалки редактирования --}}
    @foreach ($halls as $hall)
        @include('admin.halls.modals.edit', ['hall' => $hall])
    @endforeach
</div>

<style>
    /* Стили для темной темы */
    [data-theme="dark"] .modal .btn-close {
        filter: brightness(0) invert(1);
    }
</style>

@endsection

