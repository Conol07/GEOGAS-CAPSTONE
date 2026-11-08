<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only log: every price/availability update by a station
     * manager or staff member inserts a new row (no approval workflow).
     * The latest row per (station_id, fuel_type_id) is the "current" price,
     * and the full table doubles as price history.
     */
    public function up(): void
    {
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('gasoline_stations')->onDelete('cascade');
            $table->foreignId('fuel_type_id')->constrained('fuel_types')->onDelete('cascade');
            $table->decimal('price', 8, 2);
            $table->enum('availability_status', ['no_fuel', 'almost_empty', 'enough'])->default('enough');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();

            $table->index(['station_id', 'fuel_type_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
