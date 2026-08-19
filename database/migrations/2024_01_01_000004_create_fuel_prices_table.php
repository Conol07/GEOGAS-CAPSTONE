<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('gasoline_stations')->onDelete('cascade');
            $table->decimal('gasoline_price', 8, 2)->nullable();
            $table->decimal('diesel_price', 8, 2)->nullable();
            $table->decimal('premium_price', 8, 2)->nullable();
            $table->decimal('regular_price', 8, 2)->nullable();
            $table->date('effective_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('submitted_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['station_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
