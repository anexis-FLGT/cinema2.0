@extends('admin.layouts.admin')

@section('title', 'Управление фильмами')

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
    <div class="card shadow-sm border-0 p-4 bg-light rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-primary"><i class="bi bi-film me-2"></i> Управление фильмами</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMovieModal">
                <i class="bi bi-plus-circle me-1"></i> Добавить фильм
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Название</th>
                        <th>Длительность</th>
                        <th>Возраст</th>
                        <th>Жанры</th>
                        <th>Продюсер</th>
                        <th class="text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movies as $movie)
                        <tr>
                            <td>{{ $movie->id_movie }}</td>
                            <td>{{ $movie->movie_title }}</td>
                            <td>{{ $movie->duration }}</td>
                            <td>{{ $movie->age_limit }}</td>
                            <td>
                                @if($movie->genres->isNotEmpty())
                                    {{ $movie->genres->pluck('genre_name')->join(', ') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $movie->producer }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editMovieModal{{ $movie->id_movie }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.movies.destroy', $movie->id_movie) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Вы уверены, что хотите удалить этот фильм?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        {{-- Модалка редактирования --}}
                        <div class="modal fade" id="editMovieModal{{ $movie->id_movie }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.movies.update', $movie->id_movie) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold text-primary">Редактировать фильм</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Название</label>
                                                    <input type="text" name="movie_title" class="form-control" value="{{ $movie->movie_title }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Продюсер</label>
                                                    <input type="text" name="producer" class="form-control" value="{{ $movie->producer }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Длительность</label>
                                                    <input type="text" name="duration" class="form-control" value="{{ $movie->duration }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Возрастное ограничение</label>
                                                    <select name="age_limit" class="form-select" required>
                                                        @foreach(['0+','6+','12+','16+','18+'] as $age)
                                                            <option value="{{ $age }}" {{ $movie->age_limit == $age ? 'selected' : '' }}>{{ $age }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Жанры</label>
                                                    <select name="genres[]" class="form-select" multiple>
                                                        @foreach($genres as $g)
                                                            <option value="{{ $g->id_genre }}" {{ $movie->genres->contains('id_genre', $g->id_genre) ? 'selected' : '' }}>
                                                                {{ $g->genre_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Описание</label>
                                                    <textarea name="description" class="form-control" rows="3" required>{{ $movie->description }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Постер</label>
                                                    <input type="file" name="poster" class="form-control" accept="image/*">
                                                    @if($movie->poster)
                                                        <img src="{{ asset($movie->poster) }}" class="mt-2" style="max-height:80px;">
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Баннер</label>
                                                    <input type="file" name="baner" class="form-control" accept="image/*">
                                                    @if($movie->baner)
                                                        <img src="{{ asset($movie->baner) }}" class="mt-2" style="max-height:80px;">
                                                    @endif
                                                </div>
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

                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Пока фильмов нет
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        <div class="mt-3">
            {{ $movies->links() }}
        </div>
    </div>
</div>

{{-- Модалка добавления фильма --}}
<div class="modal fade" id="addMovieModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('admin.movies.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-primary">Добавить фильм</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Название</label>
              <input type="text" name="movie_title" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Продюсер</label>
              <input type="text" name="producer" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Длительность</label>
              <input type="text" name="duration" class="form-control" placeholder="1 ч. 30 мин." required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Возрастное ограничение</label>
              <select name="age_limit" class="form-select" required>
                @foreach(['0+','6+','12+','16+','18+'] as $age)
                    <option value="{{ $age }}">{{ $age }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Жанры</label>
              <select name="genres[]" class="form-select" multiple>
                @foreach($genres as $g)
                    <option value="{{ $g->id_genre }}">{{ $g->genre_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Описание</label>
              <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Постер</label>
              <input type="file" name="poster" class="form-control" accept="image/*">
            </div>
            <div class="col-md-6">
              <label class="form-label">Баннер</label>
              <input type="file" name="baner" class="form-control" accept="image/*">
            </div>
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

@endsection
