<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gasoline_stations', function (Blueprint $table) {
            // photo_path (added previously) = banner/station image
            // logo_path = small station logo used in cards, lists, popups
            $table->string('logo_path')->nullable()->after('photo_path');
            $table->text('description')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('gasoline_stations', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'description']);
        });
    }
};
