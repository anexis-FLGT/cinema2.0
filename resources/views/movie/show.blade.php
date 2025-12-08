@extends('layouts.app')

@section('title', $movie->movie_title)

@section('content')
<div class="container my-5" style="color: var(--text-primary);">
    <div class="row g-4">
        {{-- Постер --}}
        <div class="col-12 col-md-4">
            <div class="text-center">
                <img src="{{ $movie->poster }}" alt="{{ $movie->movie_title }}" 
                     class="img-fluid rounded" 
                     style="max-width: 100%; height: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
            </div>
        </div>

        {{-- Информация --}}
        <div class="col-12 col-md-8">
            <h1 class="mb-4" style="font-weight: 700; font-size: 2.5rem;">{{ $movie->movie_title }}</h1>
            
            {{-- Основная информация в одну строку --}}
            <div class="d-flex flex-wrap gap-3 mb-4" style="font-size: 1rem;">
                @if($movie->genres && $movie->genres->count() > 0)
                    <span class="badge" style="background-color: var(--accent-primary); padding: 0.5rem 1rem; font-size: 0.9rem;">
                        {{ $movie->genres->pluck('genre_name')->join(', ') }}
                    </span>
                @endif
                @if($movie->age_limit)
                    <span class="badge" style="background-color: var(--bg-secondary); color: var(--text-primary); padding: 0.5rem 1rem; font-size: 0.9rem; border: 1px solid var(--border-color);">
                        {{ $movie->age_limit }}
                    </span>
                @endif
                @if($movie->duration)
                    <span style="color: var(--text-secondary); display: flex; align-items: center;">
                        <i class="bi bi-clock me-1"></i> {{ $movie->duration }}
                    </span>
                @endif
                @if($movie->release_year)
                    <span style="color: var(--text-secondary); display: flex; align-items: center;">
                        <i class="bi bi-calendar me-1"></i> {{ $movie->release_year }}
                    </span>
                @endif
            </div>

            {{-- Детали фильма --}}
            <div class="mb-4" style="background-color: var(--bg-secondary); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
                <div class="row g-3">
                    @if($movie->director)
                    <div class="col-12 col-sm-6">
                        <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.25rem;">Режиссер</div>
                        <div style="font-weight: 500;">{{ $movie->director }}</div>
                    </div>
                    @endif
                    @if($movie->producer)
                    <div class="col-12 col-sm-6">
                        <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.25rem;">Продюсер</div>
                        <div style="font-weight: 500;">{{ $movie->producer }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Описание --}}
            @if($movie->description)
            @php
                $description = trim($movie->description);
                $maxLength = 250;
                $isLong = mb_strlen($description) > $maxLength;
                $shortDescription = $isLong ? mb_substr($description, 0, $maxLength) . '...' : $description;
            @endphp
            <div class="mb-4">
                <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 0.5rem;">О фильме</h3>
                <p id="movie-description" style="line-height: 1.8; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word; color: var(--text-secondary); margin: 0;">
                    <span id="description-short">{{ $shortDescription }}</span>
                    @if($isLong)
                    <span id="description-full" style="display: none;">{{ $description }}</span>
                    @endif
                </p>
                @if($isLong)
                <button id="toggle-description" class="btn btn-link p-0 mt-2" style="color: var(--accent-primary); text-decoration: none; font-weight: 500;">
                    <span id="toggle-text">Читать полностью</span>
                    <i class="bi bi-chevron-down ms-1" id="toggle-icon"></i>
                </button>
                @endif
            </div>
            @endif

            {{-- Кнопка --}}
            <div class="mt-4">
                <a href="{{ route('sessions') }}#movie-{{ $movie->id_movie }}" 
                   class="btn btn-danger btn-lg px-5 py-3" 
                   style="font-size: 1.1rem; font-weight: 600;">
                    <i class="bi bi-ticket-perforated me-2"></i> Перейти к сеансу
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Цвет текста описания в светлой теме */
    [data-theme="light"] #movie-description {
        color: #000000 !important;
    }
</style>

@if($movie->description && mb_strlen(trim($movie->description)) > 250)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-description');
        const shortText = document.getElementById('description-short');
        const fullText = document.getElementById('description-full');
        const toggleText = document.getElementById('toggle-text');
        const toggleIcon = document.getElementById('toggle-icon');
        let isExpanded = false;

        toggleBtn.addEventListener('click', function() {
            if (isExpanded) {
                shortText.style.display = 'inline';
                fullText.style.display = 'none';
                toggleText.textContent = 'Читать полностью';
                toggleIcon.classList.remove('bi-chevron-up');
                toggleIcon.classList.add('bi-chevron-down');
                isExpanded = false;
            } else {
                shortText.style.display = 'none';
                fullText.style.display = 'inline';
                toggleText.textContent = 'Свернуть';
                toggleIcon.classList.remove('bi-chevron-down');
                toggleIcon.classList.add('bi-chevron-up');
                isExpanded = true;
            }
        });
    });
</script>
@endif
@endsection
