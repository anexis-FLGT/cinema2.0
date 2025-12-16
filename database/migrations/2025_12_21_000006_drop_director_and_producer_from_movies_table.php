                                                                                <!DOCTYPE html>
                                                                                <html lang="en">
                                                                                <head>
                                                                                    <meta charset="UTF-8">
                                                                                    <meta name="viewport" content="width=                        Ф2, initial-scale=1.0">
                                                                                    <title>Document</title>
                                                                                </head>
                                                                                <body>
                                                                                    
                                                                                </body>
                                                                                </html><?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (Schema::hasColumn('movies', 'director')) {
                $table->dropColumn('director');
            }
            if (Schema::hasColumn('movies', 'producer')) {
                $table->dropColumn('producer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->text('producer')->nullable();
            $table->text('director')->nullable();
        });
    }
};


