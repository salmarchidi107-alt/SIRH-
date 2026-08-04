<?php
// ============================================================
//  database/migrations/xxxx_add_face_photo_to_badge_records.php
//  php artisan make:migration add_face_photo_to_badge_records --table=badge_records
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_records', function (Blueprint $table) {
            // Chemin fichier physique sur disque
            // ex : badges/{tenant}/{employee}/2026-08-04/entree_143022_a3f7b2.jpg
            $table->string('face_photo_path', 500)->nullable()->after('geolocation_denied')
                  ->comment('Chemin relatif dans le disque de stockage');

            // Disque Laravel utilisé ('public', 's3', …)
            $table->string('face_photo_disk', 50)->nullable()->default('public')->after('face_photo_path')
                  ->comment('Disque Laravel : public | s3');

            // Métadonnées
            $table->unsignedInteger('face_photo_size')->nullable()->after('face_photo_disk')
                  ->comment('Taille du fichier en octets');

            $table->string('face_photo_mime', 50)->nullable()->after('face_photo_size')
                  ->comment('Type MIME : image/jpeg | image/png | image/webp');
        });
    }

    public function down(): void
    {
        Schema::table('badge_records', function (Blueprint $table) {
            $table->dropColumn([
                'face_photo_path',
                'face_photo_disk',
                'face_photo_size',
                'face_photo_mime',
            ]);
        });
    }
};