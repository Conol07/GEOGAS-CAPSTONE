<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // local = posted by the LGU about Manolo Fortich; national = fuel news for the Philippines.
            $table->enum('scope', ['local', 'national'])->default('local')->after('category');
            $table->string('source_name', 120)->nullable()->after('scope');
            $table->string('source_url', 500)->nullable()->after('source_name');
            $table->index(['scope', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['scope', 'status', 'published_at']);
            $table->dropColumn(['scope', 'source_name', 'source_url']);
        });
    }
};
