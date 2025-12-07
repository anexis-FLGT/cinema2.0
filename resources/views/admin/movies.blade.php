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
                                                <div class="col-md-6">
                                                    <label class="form-label">Название <span class="text-danger">*</span></label>
                                                    <input type="text" name="movie_title" class="form-control" value="{{ $movie->movie_title }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Режиссер <span class="text-danger">*</span></label>
                                                    <input type="text" name="director" class="form-control" value="{{ $movie->director }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Продюсер <span class="text-danger">*</span></label>
                                                    <input type="text" name="producer" class="form-control" value="{{ $movie->producer }}" required>
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
              <label class="form-label">Название <span class="text-danger">*</span></label>
              <input type="text" name="movie_title" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Режиссер <span class="text-danger">*</span></label>
              <input type="text" name="director" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Продюсер <span class="text-danger">*</span></label>
              <input type="text" name="producer" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Длительность <span class="text-danger">*</span></label>
              <input type="text" name="duration" id="duration-input" class="form-control" placeholder="1 ч. 50 мин." required>
              <small class="text-muted">Формат: X ч. Y мин.</small>
            </div>
            <div class="col-md-4">
              <label class="form-label">Год выпуска <span class="text-danger">*</span></label>
              <input type="number" name="release_year" class="form-control" min="1900" max="{{ date('Y') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Возрастное ограничение <span class="text-danger">*</span></label>
              <select name="age_limit" class="form-select" required>
                @foreach(['0+','6+','12+','16+','18+'] as $age)
                    <option value="{{ $age }}">{{ $age }}</option>
                @endforeach
              </select>
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
              <small class="text-muted">Выберите хотя бы один жанр</small>
            </div>
            <div class="col-12">
              <label class="form-label">Описание</label>
              <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Постер <span class="text-danger">*</span></label>
              <input type="file" name="poster" class="form-control" accept="image/*" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Баннер <span class="text-danger">*</span></label>
              <input type="file" name="baner" class="form-control" accept="image/*" required>
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
        
        if (allGenreCheckboxes.length === 0) return; // Если нет checkbox'ов, пропускаем
        
        let checkedCount = 0;
        
        // Подсчитываем выбранные checkbox'ы
        allGenreCheckboxes.forEach(function(checkbox) {
            // Проверяем свойство checked напрямую
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
});
</script>

@endsection
