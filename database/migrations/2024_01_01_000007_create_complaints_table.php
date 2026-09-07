<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('name')->nullable();
            $table->string('contact')->nullable();
            $table->enum('category', [
                'incorrect_price',
                'incorrect_availability',
                'incorrect_station_info',
                'price_not_updated',
                'fuel_unavailable_despite_shown',
                'other',
            ]);
            $table->foreignId('station_id')->nullable()->constrained('gasoline_stations')->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->enum('status', ['pending', 'under_review', 'resolved'])->default('pending');
            $table->text('lgu_response')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('submitter_ip', 45)->nullable(); // basic spam/abuse tracing
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
