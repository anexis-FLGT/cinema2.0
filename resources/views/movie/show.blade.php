@extends('layouts.app')

@section('title', $movie->movie_title)

@section('content')
<div class="container my-5 text-white">
    <div class="row">
        <div class="col-md-4">
            <img src="{{ $movie->poster }}" alt="{{ $movie->movie_title }}" class="img-fluid rounded shadow">
        </div>
        <div class="col-md-8">
            <h1 class="mb-3">{{ $movie->movie_title }}</h1>

            {{-- Жанры --}}
            @if($movie->genres && $movie->genres->count() > 0)
                <p><strong>Жанр:</strong>
                    {{ $movie->genres->pluck('genre_name')->join(', ') }}
                </p>
            @else
                <p><strong>Жанр:</strong> Не указан</p>
            @endif

            <p><strong>Возрастное ограничение:</strong> {{ $movie->age_limit }}</p>
            <p><strong>Длительность:</strong> {{ $movie->duration }}</p>
            <p><strong>Продюсер:</strong> {{ $movie->producer }}</p>

            <p class="mt-4">{{ $movie->description }}</p>

            <a href="{{ route('sessions') }}#movie-{{ $movie->id_movie }}" class="btn btn-danger mt-3">
                <i class="bi bi-ticket-perforated"></i> Забронировать билет
            </a>
        </div>
    </div>
</div>
@endsection
