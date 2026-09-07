<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('gasoline_stations')->onDelete('cascade');
            $table->string('service_key');   // e.g. store, mechanic, motor_oil, car_oil, cr, air_pump...
            $table->string('label');
            $table->boolean('available')->default(false);
            $table->timestamps();

            $table->unique(['station_id', 'service_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_services');
    }
};
