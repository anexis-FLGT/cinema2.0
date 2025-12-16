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
    <div class="card shadow-sm border-0 p-4 rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-primary"><i class="bi bi-film me-2"></i> Управление фильмами</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMovieModal">
                <i class="bi bi-plus-circle me-1"></i> Добавить фильм
            </button>
        </div>

        {{-- Форма поиска и фильтрации --}}
        <form method="GET" action="{{ route('admin.movies.index') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Поиск по названию</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Введите название фильма..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Фильтр по жанру</label>
                    <select name="genre_id" class="form-select">
                        <option value="">Все жанры</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id_genre }}" @if(request('genre_id') == $genre->id_genre) selected @endif>{{ $genre->genre_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary text-white w-100">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                </div>
                @if(request('search') || request('genre_id'))
                    <div class="col-12">
                        <a href="{{ route('admin.movies.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Сбросить фильтры
                        </a>
                    </div>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-primary">
                    <tr>
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
                            <td>
                                @php
                                    $producersText = $movie->producers?->pluck('name')->join(', ');
                                @endphp
                                {{ $producersText ?: '—' }}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editMovieModal{{ $movie->id_movie }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteMovieModal{{ $movie->id_movie }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                
                                {{-- Модалка удаления фильма --}}
                                <div class="modal fade" id="deleteMovieModal{{ $movie->id_movie }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.movies.destroy', $movie->id_movie) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">Удалить фильм?</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Вы уверены, что хотите удалить фильм <strong>{{ $movie->movie_title }}</strong>?</p>
                                                    @php
                                                        $activeSessionsCount = $movie->sessions()->where('date_time_session', '>', now())->count();
                                                    @endphp
                                                    @if($activeSessionsCount > 0)
                                                        <div class="alert alert-warning mt-3">
                                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                                            <strong>Внимание!</strong> У этого фильма есть <strong>{{ $activeSessionsCount }}</strong> {{ $activeSessionsCount == 1 ? 'активный сеанс' : ($activeSessionsCount < 5 ? 'активных сеанса' : 'активных сеансов') }}.
                                                            <br>При удалении фильма все активные сеансы также будут удалены!
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
                                                <div class="col-12">
                                                    <label class="form-label">Название <span class="text-danger">*</span></label>
                                                    <input type="text" name="movie_title" class="form-control" value="{{ $movie->movie_title }}" required>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label">Режиссёры <span class="text-danger">*</span></label>
                                                    <div class="directors-checkbox-container" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; background: var(--input-bg);">
                                                        @foreach($allDirectors as $director)
                                                            <label class="form-check mb-2 director-item" for="edit_director_{{ $movie->id_movie }}_{{ $director->id_director }}" style="display: block; cursor: pointer; padding: 8px; margin: 0 -8px; border-radius: 4px;">
                                                                <input class="form-check-input director-checkbox" type="checkbox" name="directors[]" value="{{ $director->id_director }}" id="edit_director_{{ $movie->id_movie }}_{{ $director->id_director }}" {{ $movie->directors->contains('id_director', $director->id_director) ? 'checked' : '' }}>
                                                                <span class="form-check-label">
                                                                    {{ $director->name }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    <small class="text-muted d-block mt-1">Выберите одного или нескольких режиссёров</small>
                                                    <label class="form-label mt-2">Новые режиссёры (через запятую)</label>
                                                    <input type="text" name="new_directors" class="form-control">
                                                    @error('directors')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <label class="form-label">Продюсеры <span class="text-danger">*</span></label>
                                                    <div class="producers-checkbox-container" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; background: var(--input-bg);">
                                                        @foreach($allProducers as $producer)
                                                            <label class="form-check mb-2 producer-item" for="edit_producer_{{ $movie->id_movie }}_{{ $producer->id_producer }}" style="display: block; cursor: pointer; padding: 8px; margin: 0 -8px; border-radius: 4px;">
                                                                <input class="form-check-input producer-checkbox" type="checkbox" name="producers[]" value="{{ $producer->id_producer }}" id="edit_producer_{{ $movie->id_movie }}_{{ $producer->id_producer }}" {{ $movie->producers->contains('id_producer', $producer->id_producer) ? 'checked' : '' }}>
                                                                <span class="form-check-label">
                                                                    {{ $producer->name }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    <small class="text-muted d-block mt-1">Выберите одного или нескольких продюсеров</small>
                                                    <label class="form-label mt-2">Новые продюсеры (через запятую)</label>
                                                    <input type="text" name="new_producers" class="form-control">
                                                    @error('producers')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <label class="form-label">Длительность <span class="text-danger">*</span></label>
                                                    <input type="text" name="duration" class="form-control duration-input" value="{{ $movie->duration }}" required>
                                                    <small class="text-muted">Формат: X ч. Y мин.</small>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Год выпуска <span class="text-danger">*</span></label>
                                                    <input type="number" name="release_year" class="form-control" value="{{ $movie->release_year }}" min="1900" max="{{ date('Y') }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Возрастное ограничение <span class="text-danger">*</span></label>
                                                    <select name="age_limit" class="form-select" required>
                                                        @foreach(['0+','6+','12+','16+','18+'] as $age)
                                                            <option value="{{ $age }}" {{ $movie->age_limit == $age ? 'selected' : '' }}>{{ $age }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Жанры <span class="text-danger">*</span></label>
                                                    <div class="genres-checkbox-container" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; background: var(--input-bg);">
                                                        @foreach($genres as $g)
                                                            <label class="form-check mb-2 genre-item" for="edit_genre_{{ $movie->id_movie }}_{{ $g->id_genre }}" style="display: block; cursor: pointer; padding: 8px; margin: 0 -8px; border-radius: 4px;">
                                                                <input class="form-check-input genre-checkbox" type="checkbox" name="genres[]" value="{{ $g->id_genre }}" id="edit_genre_{{ $movie->id_movie }}_{{ $g->id_genre }}" {{ $movie->genres->contains('id_genre', $g->id_genre) ? 'checked' : '' }}>
                                                                <span class="form-check-label" style="margin-left: 8px;">
                                                                    {{ $g->genre_name }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    @error('genres')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                    <small class="text-muted">Выберите хотя бы один жанр</small>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Описание</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $movie->description }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Постер</label>
                                                    <input type="file" name="poster" class="form-control" accept="image/*">
                                                    @if($movie->poster)
                                                        <img src="{{ asset($movie->poster) }}" class="mt-2" style="max-height:80px;">
                                                    @endif
                                                    <small class="text-muted">Оставьте пустым, чтобы сохранить текущий</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Баннер</label>
                                                    <input type="file" name="baner" class="form-control" accept="image/*">
                                                    @if($movie->baner)
                                                        <img src="{{ asset($movie->baner) }}" class="mt-2" style="max-height:80px;">
                                                    @endif
                                                    <small class="text-muted">Оставьте пустым, чтобы сохранить текущий</small>
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
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Пока фильмов нет
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($movies->hasPages())
            <div class="mt-4">
                {{ $movies->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif
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
            <div class="col-12">
              <label class="form-label">Название <span class="text-danger">*</span></label>
              <input type="text" name="movie_title" class="form-control @error('movie_title') is-invalid @enderror" value="{{ old('movie_title') }}" required>
              @error('movie_title')
                  <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Режиссёры <span class="text-danger">*</span></label>
              <div class="directors-checkbox-container" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; background: var(--input-bg);">
                @foreach($allDirectors as $director)
                    <label class="form-check mb-2 director-item" for="director_{{ $director->id_director }}" style="display: block; cursor: pointer; padding: 8px; margin: 0 -8px; border-radius: 4px;">
                        <input class="form-check-input director-checkbox" type="checkbox" name="directors[]" value="{{ $director->id_director }}" id="director_{{ $director->id_director }}"
                            @if(collect(old('directors', []))->contains($director->id_director)) checked @endif>
                        <span class="form-check-label">
                            {{ $director->name }}
                        </span>
                    </label>
                @endforeach
              </div>
              <small class="text-muted d-block mt-1">Выберите одного или нескольких режиссёров</small>
              <label class="form-label mt-2">Новые режиссёры (через запятую)</label>
              <input type="text" name="new_directors" class="form-control" value="{{ old('new_directors') }}" >
              @error('directors')
                  <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Продюсеры <span class="text-danger">*</span></label>
              <div class="producers-checkbox-container" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; background: var(--input-bg);">
                @foreach($allProducers as $producer)
                    <label class="form-check mb-2 producer-item" for="producer_{{ $producer->id_producer }}" style="display: block; cursor: pointer; padding: 8px; margin: 0 -8px; border-radius: 4px;">
                        <input class="form-check-input producer-checkbox" type="checkbox" name="producers[]" value="{{ $producer->id_producer }}" id="producer_{{ $producer->id_producer }}"
                            @if(collect(old('producers', []))->contains($producer->id_producer)) checked @endif>
                        <span class="form-check-label">
                            {{ $producer->name }}
                        </span>
                    </label>
                @endforeach
              </div>
              <small class="text-muted d-block mt-1">Выберите одного или нескольких продюсеров</small>
              <label class="form-label mt-2">Новые продюсеры (через запятую)</label>
              <input type="text" name="new_producers" class="form-control" value="{{ old('new_producers') }}">
              @error('producers')
                  <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="col-md-4">
              <label class="form-label">Длительность <span class="text-danger">*</span></label>
              <input type="text" name="duration" id="duration-input" class="form-control @error('duration') is-invalid @enderror" placeholder="1 ч. 50 мин." value="{{ old('duration') }}" required>
              @error('duration')
                  <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Формат: X ч. Y мин.</small>
            </div>
            <div class="col-md-4">
              <label class="form-label">Год выпуска <span class="text-danger">*</span></label>
              <input type="number" name="release_year" class="form-control @error('release_year') is-invalid @enderror" min="1900" max="{{ date('Y') }}" value="{{ old('release_year') }}" required>
              @error('release_year')
                  <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Возрастное ограничение <span class="text-danger">*</span></label>
              <select name="age_limit" class="form-select @error('age_limit') is-invalid @enderror" required>
                @foreach(['0+','6+','12+','16+','18+'] as $age)
                    <option value="{{ $age }}" @if(old('age_limit') == $age) selected @endif>{{ $age }}</option>
                @endforeach
              </select>
              @error('age_limit')
                  <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-12">
              <label class="form-label">Жанры <span class="text-danger">*</span></label>
              <div class="genres-checkbox-container" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; background: var(--input-bg);">
                @foreach($genres as $g)
                    <label class="form-check mb-2 genre-item" for="genre_{{ $g->id_genre }}" style="display: block; cursor: pointer; padding: 8px; margin: 0 -8px; border-radius: 4px;">
                        <input class="form-check-input genre-checkbox" type="checkbox" name="genres[]" value="{{ $g->id_genre }}" id="genre_{{ $g->id_genre }}">
                        <span class="form-check-label" style="margin-left: 8px;">
                            {{ $g->genre_name }}
                        </span>
                    </label>
                @endforeach
              </div>
              <input type="hidden" name="genres_check" value="1">
              @error('genres')
                  <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
              <small class="text-muted">Выберите хотя бы один жанр</small>
            </div>
            <div class="col-12">
              <label class="form-label">Описание</label>
              <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
              @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Постер <span class="text-danger">*</span></label>
              <input type="file" name="poster" class="form-control @error('poster') is-invalid @enderror" accept="image/jpeg,image/jpg,image/png" required>
              @error('poster')
                  <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Баннер</label>
              <input type="file" name="baner" class="form-control @error('baner') is-invalid @enderror" accept="image/jpeg,image/jpg,image/png">
              @error('baner')
                  <div class="invalid-feedback">{{ $message }}</div>
              @enderror
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Автоматически открываем модальное окно добавления, если есть ошибки валидации
    @if($errors->any() && !session('edit_movie_id'))
        const addMovieModal = new bootstrap.Modal(document.getElementById('addMovieModal'));
        addMovieModal.show();
    @endif

    // Валидация жанров - проверка, что выбран хотя бы один жанр
    // Используем делегирование событий на уровне document
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Проверяем, что это форма для фильмов
        if (!form || form.tagName !== 'FORM') return;
        
        const formAction = form.getAttribute('action') || '';
        if (!formAction.includes('movies')) return;
        
        // Ищем все checkbox'ы жанров внутри этой формы
        const allGenreCheckboxes = form.querySelectorAll('input[type="checkbox"][name="genres[]"]');
        
        if (allGenreCheckboxes.length > 0) {
            let checkedCount = 0;
            
            // Подсчитываем выбранные checkbox'ы
            allGenreCheckboxes.forEach(function(checkbox) {
                if (checkbox.checked === true) {
                    checkedCount++;
                }
            });
            
            // Проверяем, что есть хотя бы один выбранный checkbox
            if (checkedCount === 0) {
                e.preventDefault();
                e.stopPropagation();
                alert('Пожалуйста, выберите хотя бы один жанр');
                return false;
            }
        }
        
        // Валидация режиссёров
        const allDirectorCheckboxes = form.querySelectorAll('input[type="checkbox"][name="directors[]"]');
        const newDirectorsInput = form.querySelector('input[name="new_directors"]');
        
        if (allDirectorCheckboxes.length > 0) {
            let checkedDirectorsCount = 0;
            
            allDirectorCheckboxes.forEach(function(checkbox) {
                if (checkbox.checked === true) {
                    checkedDirectorsCount++;
                }
            });
            
            const hasNewDirectors = newDirectorsInput && newDirectorsInput.value.trim().length > 0;
            
            if (checkedDirectorsCount === 0 && !hasNewDirectors) {
                e.preventDefault();
                e.stopPropagation();
                alert('Пожалуйста, выберите хотя бы одного режиссёра или укажите нового');
                return false;
            }
        }
        
        // Валидация продюсеров
        const allProducerCheckboxes = form.querySelectorAll('input[type="checkbox"][name="producers[]"]');
        const newProducersInput = form.querySelector('input[name="new_producers"]');
        
        if (allProducerCheckboxes.length > 0) {
            let checkedProducersCount = 0;
            
            allProducerCheckboxes.forEach(function(checkbox) {
                if (checkbox.checked === true) {
                    checkedProducersCount++;
                }
            });
            
            const hasNewProducers = newProducersInput && newProducersInput.value.trim().length > 0;
            
            if (checkedProducersCount === 0 && !hasNewProducers) {
                e.preventDefault();
                e.stopPropagation();
                alert('Пожалуйста, выберите хотя бы одного продюсера или укажите нового');
                return false;
            }
        }
    }, false);
    
    // Функция проверки, является ли поле полем длительности
    function isDurationField(element) {
        if (!element || element.tagName !== 'INPUT') return false;
        return element.name === 'duration' || 
               element.classList.contains('duration-input') || 
               element.id === 'duration-input';
    }
    
    // Функция форматирования длительности
    function formatDuration(digitsOnly) {
        if (digitsOnly.length === 0) return '';
        
        if (digitsOnly.length <= 2) {
            // 1-2 цифры - это часы
            return digitsOnly + ' ч.';
        } else if (digitsOnly.length <= 4) {
            // 3-4 цифры - первые 1-2 это часы, остальные минуты
            const hours = digitsOnly.slice(0, -2);
            const minutes = digitsOnly.slice(-2);
            return hours + ' ч. ' + minutes + ' мин.';
        } else {
            // Больше 4 цифр - берем последние 4
            const hours = digitsOnly.slice(-4, -2);
            const minutes = digitsOnly.slice(-2);
            return hours + ' ч. ' + minutes + ' мин.';
        }
    }
    
    // Запрещаем ввод недопустимых символов
    document.addEventListener('keydown', function(e) {
        if (!isDurationField(e.target)) return;
        
        const key = e.key;
        const code = e.keyCode || e.which;
        
        // Разрешаем цифры
        if (key >= '0' && key <= '9') {
            return true;
        }
        
        // Разрешаем служебные клавиши
        if (code === 8 || code === 9 || code === 13 || code === 27 || 
            code === 37 || code === 38 || code === 39 || code === 40 || 
            code === 46 || e.ctrlKey || e.metaKey || e.altKey) {
            return true;
        }
        
        // Блокируем все остальные символы
        e.preventDefault();
        return false;
    }, true);
    
    // Обработчик ввода - автоматическое форматирование в реальном времени
    // Используем capture: false, чтобы не мешать другим обработчикам
    document.addEventListener('input', function(e) {
        if (!isDurationField(e.target)) return;
        
        const input = e.target;
        let value = input.value;
        
        // Удаляем все символы кроме цифр
        const digitsOnly = value.replace(/\D/g, '');
        
        if (digitsOnly.length === 0) {
            input.value = '';
            return;
        }
        
        // Форматируем значение
        const formattedValue = formatDuration(digitsOnly);
        input.value = formattedValue;
    }, false);
    
    // Обработчик при потере фокуса - финальная проверка и форматирование
    // Используем capture: false, чтобы не мешать другим обработчикам
    document.addEventListener('focusout', function(e) {
        if (!isDurationField(e.target)) return;
        
        const input = e.target;
        let value = input.value.trim();
        
        // Извлекаем только цифры
        const digits = value.replace(/\D/g, '');
        
        if (digits.length === 0) {
            input.value = '';
            return;
        }
        
        // Форматируем в правильный формат
        if (digits.length <= 2) {
            // Только часы
            input.value = digits + ' ч. 0 мин.';
        } else {
            // Часы и минуты
            const hours = digits.slice(0, -2);
            let minutes = parseInt(digits.slice(-2));
            
            // Если минут больше 59, конвертируем в часы
            if (minutes > 59) {
                const extraHours = Math.floor(minutes / 60);
                const remainingMinutes = minutes % 60;
                const totalHours = parseInt(hours) + extraHours;
                input.value = totalHours + ' ч. ' + remainingMinutes + ' мин.';
            } else {
                input.value = hours + ' ч. ' + minutes + ' мин.';
            }
        }
    }, false);
    
    // Применяем маску к существующим полям
    function applyMaskToExistingFields() {
        const durationInputs = document.querySelectorAll('input[name="duration"], .duration-input, #duration-input');
        durationInputs.forEach(function(input) {
            // Маска уже применяется через делегирование событий
        });
    }
    
    // Применяем при загрузке
    applyMaskToExistingFields();
    
    // Применяем при открытии модальных окон
    document.addEventListener('shown.bs.modal', function(e) {
        applyMaskToExistingFields();
    });
    
    // Логика для красного индикатора слева у режиссёров и продюсеров (как у жанров)
    function updateCheckboxIndicator(checkbox) {
        const label = checkbox.closest('.director-item, .producer-item');
        if (label) {
            if (checkbox.checked) {
                label.style.setProperty('border-left', '3px solid var(--accent-primary)', 'important');
                label.style.setProperty('padding-left', '13px', 'important');
            } else {
                label.style.setProperty('border-left', '3px solid transparent', 'important');
                label.style.setProperty('padding-left', '8px', 'important');
            }
        }
    }
    
    // Обработчик для всех чекбоксов режиссёров и продюсеров
    document.addEventListener('change', function(e) {
        if (e.target && (e.target.classList.contains('director-checkbox') || e.target.classList.contains('producer-checkbox'))) {
            updateCheckboxIndicator(e.target);
        }
    });
    
    // Инициализация: применяем стили для всех чекбоксов
    function initializeCheckboxIndicators() {
        const allCheckboxes = document.querySelectorAll('.director-checkbox, .producer-checkbox');
        allCheckboxes.forEach(function(checkbox) {
            updateCheckboxIndicator(checkbox);
        });
    }
    
    // Применяем при загрузке страницы
    initializeCheckboxIndicators();
    
    // Применяем при открытии модальных окон
    document.addEventListener('shown.bs.modal', function(e) {
        setTimeout(function() {
            initializeCheckboxIndicators();
        }, 100);
    });
});
</script>

<style>
    /* Стили для контейнеров с чекбоксами режиссёров и продюсеров (скопировано из жанров) */
    .directors-checkbox-container,
    .producers-checkbox-container {
        background: var(--input-bg) !important;
        border: 1px solid var(--border-color) !important;
    }
    
    .directors-checkbox-container .form-check,
    .directors-checkbox-container .director-item,
    .producers-checkbox-container .form-check,
    .producers-checkbox-container .producer-item {
        padding: 8px;
        margin: 0 -8px;
        position: relative;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }
    
    .directors-checkbox-container .form-check:hover,
    .directors-checkbox-container .director-item:hover,
    .producers-checkbox-container .form-check:hover,
    .producers-checkbox-container .producer-item:hover {
        background: var(--bg-tertiary);
    }
    
    .directors-checkbox-container .form-check-input,
    .producers-checkbox-container .form-check-input {
        position: absolute;
        left: -9999px;
        opacity: 0;
        visibility: hidden;
    }
    
    .directors-checkbox-container .director-item,
    .producers-checkbox-container .producer-item {
        border-left: 3px solid transparent;
        padding-left: 8px;
        transition: border-left 0.2s ease, padding-left 0.2s ease;
    }
    
    .directors-checkbox-container .form-check-label,
    .producers-checkbox-container .form-check-label {
        position: relative;
        z-index: 1;
        margin-left: 0;
    }
    
    /* Стили для темной темы в модалках фильмов */
    [data-theme="dark"] #addMovieModal .btn-close,
    [data-theme="dark"] .modal[id^="editMovieModal"] .btn-close {
        filter: brightness(0) invert(1);
    }
    
    /* Белый цвет плейсхолдера для длительности в темной теме */
    [data-theme="dark"] #addMovieModal input[name="duration"]::placeholder,
    [data-theme="dark"] .modal[id^="editMovieModal"] input[name="duration"]::placeholder,
    [data-theme="dark"] #addMovieModal #duration-input::placeholder,
    [data-theme="dark"] .modal[id^="editMovieModal"] .duration-input::placeholder {
        color: rgba(255, 255, 255, 0.6) !important;
    }
    
    /* Белый цвет текста в поле длительности в темной теме */
    [data-theme="dark"] #addMovieModal input[name="duration"],
    [data-theme="dark"] .modal[id^="editMovieModal"] input[name="duration"],
    [data-theme="dark"] #addMovieModal #duration-input,
    [data-theme="dark"] .modal[id^="editMovieModal"] .duration-input {
        color: #ffffff !important;
    }

    /* Стили для поиска - адаптация под темы */
    [data-theme="dark"] .input-group-text {
        background-color: var(--bg-secondary) !important;
        border-color: var(--border-color) !important;
        color: #ffffff !important;
    }
    
    [data-theme="dark"] .input-group-text i {
        color: #ffffff !important;
    }
    
    [data-theme="dark"] .input-group .form-control {
        background-color: var(--input-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .input-group .form-control::placeholder {
        color: #ffffff !important;
        opacity: 0.7;
    }
    
    [data-theme="dark"] .input-group .form-control:focus {
        background-color: var(--input-bg) !important;
        border-color: var(--input-focus-border) !important;
        color: var(--text-primary) !important;
    }
    
</style>

@endsection
