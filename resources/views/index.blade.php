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
                    <div class="banner-content text-center text-white">
                        <h1 class="banner-title">{{ $banner->movie_title }}</h1>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Навигация --}}
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>

{{-- Афиша фильмов --}}
<section class="movie-section container py-5">
    <h2 class="section-title text-white mb-4">Афиша фильмов</h2>

    {{-- Форма поиска и фильтров --}}
    <div class="filters-container mb-4">
        <form method="GET" action="{{ route('home') }}" class="filters-form">
            <div class="row g-3">
                {{-- Поиск по названию --}}
                <div class="col-12 col-md-4">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Поиск по названию..." 
                           value="{{ request('search') }}">
                </div>

                {{-- Фильтр по жанру --}}
                <div class="col-12 col-md-3">
                    <select name="genre" class="form-select">
                        <option value="">Все жанры</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id_genre }}" {{ request('genre') == $genre->id_genre ? 'selected' : '' }}>
                                {{ $genre->genre_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Фильтр по длительности --}}
                <div class="col-12 col-md-2">
                    <input type="number" 
                           name="duration_min" 
                           class="form-control" 
                           placeholder="Мин. (мин)" 
                           min="0"
                           value="{{ request('duration_min') }}">
                </div>
                <div class="col-12 col-md-2">
                    <input type="number" 
                           name="duration_max" 
                           class="form-control" 
                           placeholder="Макс. (мин)" 
                           min="0"
                           value="{{ request('duration_max') }}">
                </div>

                {{-- Фильтр по дате показа --}}
                <div class="col-12 col-md-3">
                    <input type="date" 
                           name="show_date" 
                           class="form-control" 
                           value="{{ request('show_date') }}">
                </div>

                {{-- Сортировка --}}
                <div class="col-12 col-md-3">
                    <select name="sort" class="form-select">
                        <option value="alphabet" {{ request('sort') == 'alphabet' ? 'selected' : '' }}>По алфавиту (А-Я)</option>
                        <option value="alphabet_desc" {{ request('sort') == 'alphabet_desc' ? 'selected' : '' }}>По алфавиту (Я-А)</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Сначала новые</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Сначала старые</option>
                    </select>
                </div>

                {{-- Кнопки --}}
                <div class="col-12 col-md-6">
                    <button type="submit" class="btn btn-primary me-2">Применить фильтры</button>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">Сбросить</a>
                </div>
            </div>
        </form>
    </div>

    @if($movies->isEmpty())
        <div class="alert alert-info text-center">
            <p class="mb-0">Фильмы не найдены. Попробуйте изменить параметры поиска или фильтры.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach ($movies as $movie)
                <div class="col-6 col-md-3">
                    <div class="movie-card">
                        <img src="{{ $movie->poster }}" alt="{{ $movie->movie_title }}" class="movie-poster">
                        <div class="movie-info text-white p-3">
                            <h5 class="movie-title">{{ $movie->movie_title }}</h5>
                            <p class="movie-meta">
                                @if($movie->genres && $movie->genres->isNotEmpty())
                                    Жанр: {{ $movie->genres->pluck('genre_name')->join(', ') }} | 
                                @else
                                    Жанр: Не указан | 
                                @endif
                                {{ $movie->age_limit ?? '0+' }} | {{ $movie->duration ?? '' }}
                            </p>
                            <a href="{{ route('movie.show', $movie->id_movie) }}" class="btn btn-outline-light w-100 mt-2">Подробнее</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
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
