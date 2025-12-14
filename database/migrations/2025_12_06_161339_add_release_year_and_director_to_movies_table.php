<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            // Добавляем год выпуска после duration
            $table->integer('release_year')->nullable()->after('duration');
            // Добавляем режиссера после description (TEXT(300) по ER-диаграмме, используем text)
            $table->text('director')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['release_year', 'director']);
        });
    }
};
