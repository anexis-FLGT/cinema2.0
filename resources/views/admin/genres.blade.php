@extends('admin.layouts.admin')

@section('title', 'Управление жанрами')

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
            <h4 class="fw-bold text-info"><i class="bi bi-tags me-2"></i> Управление жанрами</h4>
            <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addGenreModal">
                <i class="bi bi-plus-circle me-1"></i> Добавить жанр
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-info">
                    <tr>
                        <th>Название жанра</th>
                        <th>Количество фильмов</th>
                        <th class="text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($genres as $genre)
                        <tr>
                            <td>{{ $genre->genre_name }}</td>
                            <td>{{ $genre->movies_count }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editGenreModal{{ $genre->id_genre }}">
                                    <i class="bi bi-pencil"></i> Редактировать
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteGenreModal{{ $genre->id_genre }}">
                                    <i class="bi bi-trash"></i> Удалить
                                </button>
                            </td>
                        </tr>

                        {{-- Модальное окно редактирования жанра --}}
                        <div class="modal fade" id="editGenreModal{{ $genre->id_genre }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.genres.update', $genre->id_genre) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Редактировать жанр</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Название жанра <span class="text-danger">*</span></label>
                                                <input type="text" name="genre_name" class="form-control" value="{{ old('genre_name', $genre->genre_name) }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Сохранить</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Модальное окно удаления жанра --}}
                        <div class="modal fade" id="deleteGenreModal{{ $genre->id_genre }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.genres.destroy', $genre->id_genre) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger">Удалить жанр?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Вы уверены, что хотите удалить жанр <strong>{{ $genre->genre_name }}</strong>?</p>
                                            @php
                                                $moviesCount = $genre->movies()->count();
                                            @endphp
                                            @if($moviesCount > 0)
                                                <div class="alert alert-warning mt-3">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    <strong>Внимание!</strong> Этот жанр используется в {{ $moviesCount }} {{ $moviesCount == 1 ? 'фильме' : 'фильмах' }}. 
                                                    При удалении жанра он будет удалён из всех связанных фильмов.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-danger">Удалить</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">
                                <p class="text-muted mb-0">Жанры не найдены</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($genres->hasPages())
            <div class="mt-4">
                {{ $genres->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

{{-- Модальное окно добавления жанра --}}
<div class="modal fade" id="addGenreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.genres.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Добавить жанр</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Название жанра <span class="text-danger">*</span></label>
                        <input type="text" name="genre_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Добавить</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('error') && old('genre_name'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('addGenreModal'));
        modal.show();
    });
</script>
@endif
@endsection


