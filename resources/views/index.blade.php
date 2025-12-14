@extends('layouts.app')

@section('title', 'MaxTicket — Главная')

@section('content')

{{-- Hero / Banner --}}
<div class="banner-container">
    <div class="swiper bannerSwiper">
        <div class="swiper-wrapper">
            @foreach ($banners as $banner)
                <div class="swiper-slide banner-slide" style="background-image: url('{{ $banner->baner }}');">
                    <div class="banner-overlay"></div>
                    
                </div>
            @endforeach
        </div>

        {{-- Навигация --}}
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>

{{-- Новинки --}}
<section class="movie-section container py-5">
    <div class="row">
        {{-- Боковая панель фильтров --}}
        <aside class="col-12 col-lg-3 mb-4 mb-lg-0">
            <div class="filters-sidebar">
                <h3 class="filters-title mb-3">Фильтры и поиск</h3>
                
                <form method="GET" action="{{ route('home') }}" class="filters-form" id="filtersForm">
                    {{-- Поиск по названию --}}
                    <div class="filter-group mb-3">
                        <label class="filter-label">Поиск</label>
                        <input type="text" 
                               name="search" 
                               class="form-control form-control-sm" 
                               placeholder="Название фильма..." 
                               value="{{ request('search') }}">
                    </div>

                    {{-- Фильтр по жанру --}}
                    <div class="filter-group mb-3">
                        <label class="filter-label">Жанр</label>
                        <select name="genre" class="form-select form-select-sm">
                            <option value="">Все жанры</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id_genre }}" {{ request('genre') == $genre->id_genre ? 'selected' : '' }}>
                                    {{ $genre->genre_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Фильтр по длительности --}}
                    <div class="filter-group mb-3">
                        <label class="filter-label">Длительность</label>
                        <div class="d-flex gap-2">
                            <input type="text" 
                                   name="duration_min" 
                                   class="form-control form-control-sm" 
                                   placeholder="От" 
                                   value="{{ request('duration_min') }}">
                            <input type="text" 
                                   name="duration_max" 
                                   class="form-control form-control-sm" 
                                   placeholder="До" 
                                   value="{{ request('duration_max') }}">
                        </div>
                        <small class="text-white" style="font-size: 0.75rem; display: block; margin-top: 0.25rem; opacity: 0.8;">
                            Формат: 1ч. 30 мин, 1:30, 90 мин или 90
                        </small>
                    </div>

                    {{-- Сортировка (радио-кнопки) --}}
                    <div class="filter-group mb-3">
                        <label class="filter-label">Сортировка</label>
                        <div class="sort-options">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="sort" 
                                       id="sort_newest" 
                                       value="newest" 
                                       {{ request('sort', 'newest') == 'newest' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sort_newest">
                                    Сначала новые
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="sort" 
                                       id="sort_oldest" 
                                       value="oldest" 
                                       {{ request('sort') == 'oldest' ? 'checked' : '' }}>
                                <label class="form-check-label" for="sort_oldest">
                                    Сначала старые
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Кнопки --}}
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary btn-sm w-100 mb-2">Применить</button>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm w-100">Сбросить</a>
                    </div>
                </form>
            </div>
        </aside>

        {{-- Основной контент с афишей --}}
        <div class="col-12 col-lg-9">
            <h2 class="section-title text-white mb-4">Все фильмы</h2>

            @if(session('no_results'))
                <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Фильмы не найдены!</strong> По выбранным критериям фильмы не найдены. Попробуйте изменить параметры поиска или фильтры.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($movies->isEmpty())
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    <p class="mb-0">Фильмы не найдены. Попробуйте изменить параметры поиска или фильтры.</p>
                </div>
            @else
                <div class="movies-horizontal-list">
                    @foreach ($movies as $movie)
                        <div class="movie-card-horizontal">
                            <div class="movie-poster-block">
                                <img src="{{ $movie->poster }}" alt="{{ $movie->movie_title }}" class="movie-poster-img">
                            </div>
                            <div class="movie-info-block">
                                <h3 class="movie-title-horizontal">{{ $movie->movie_title }}</h3>
                                <div class="movie-details-list">
                                    @if($movie->genres && $movie->genres->isNotEmpty())
                                        <div class="movie-detail-row">
                                            <span class="detail-label">Жанр:</span>
                                            <span class="detail-value">{{ $movie->genres->pluck('genre_name')->join(', ') }}</span>
                                        </div>
                                    @endif
                                    <div class="movie-detail-row">
                                        <span class="detail-label">Возраст:</span>
                                        <span class="detail-value">{{ $movie->age_limit ?? '0+' }}</span>
                                    </div>
                                    <div class="movie-detail-row">
                                        <span class="detail-label">Длительность:</span>
                                        <span class="detail-value">{{ $movie->duration ?? 'Не указано' }}</span>
                                    </div>
                                    @if($movie->release_year)
                                        <div class="movie-detail-row">
                                            <span class="detail-label">Год выпуска:</span>
                                            <span class="detail-value">{{ $movie->release_year }}</span>
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('movie.show', $movie->id_movie) }}" class="btn btn-outline-light mt-3">Подробнее</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Swiper JS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    const swiper = new Swiper('.bannerSwiper', {
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        effect: 'fade',
        fadeEffect: { crossFade: true },
    });
</script>
@endsection
