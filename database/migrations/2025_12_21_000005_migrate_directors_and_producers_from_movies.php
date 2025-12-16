<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Берём все фильмы и раскладываем режиссёров/продюсеров по новым таблицам
        $movies = DB::table('movies')->select('id_movie', 'director', 'producer')->get();

        foreach ($movies as $movie) {
            // Режиссёры
            $directorNames = $this->splitNames($movie->director);
            foreach ($directorNames as $name) {
                $directorId = $this->firstOrCreate('directors', 'id_director', $name);
                if ($directorId) {
                    DB::table('director_movie')->updateOrInsert(
                        ['movie_id' => $movie->id_movie, 'director_id' => $directorId],
                        []
                    );
                }
            }

            // Продюсеры
            $producerNames = $this->splitNames($movie->producer);
            foreach ($producerNames as $name) {
                $producerId = $this->firstOrCreate('producers', 'id_producer', $name);
                if ($producerId) {
                    DB::table('movie_producer')->updateOrInsert(
                        ['movie_id' => $movie->id_movie, 'producer_id' => $producerId],
                        []
                    );
                }
            }
        }
    }

    public function down(): void
    {
        // При откате просто чистим связи и справочники
        DB::table('director_movie')->truncate();
        DB::table('movie_producer')->truncate();
        DB::table('directors')->truncate();
        DB::table('producers')->truncate();
    }

    private function splitNames(?string $raw): array
    {
        if (!$raw) {
            return [];
        }
        // Разделяем по запятым и точкам с запятой
        $parts = preg_split('/[;,]+/', $raw);
        return collect($parts)
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function firstOrCreate(string $table, string $idColumn, string $name): ?int
    {
        $existing = DB::table($table)->where('name', $name)->first();
        if ($existing) {
            return $existing->{$idColumn};
        }

        return DB::table($table)->insertGetId(['name' => $name]);
    }
};


