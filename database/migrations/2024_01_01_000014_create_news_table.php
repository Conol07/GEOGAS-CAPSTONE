<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', [
                'price_update', 'price_hike', 'price_decrease', 'new_station',
                'station_update', 'fuel_availability', 'lgu_announcement', 'important_notice', 'other',
            ]);
            $table->text('content');
            $table->string('image_path')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('related_station_id')->nullable()->constrained('gasoline_stations')->nullOnDelete();
            $table->string('related_barangay')->nullable();
            $table->foreignId('related_fuel_type_id')->nullable()->constrained('fuel_types')->nullOnDelete();
            $table->decimal('previous_price', 8, 2)->nullable();
            $table->decimal('current_price', 8, 2)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
