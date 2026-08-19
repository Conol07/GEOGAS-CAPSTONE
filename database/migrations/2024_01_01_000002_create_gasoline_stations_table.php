<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gasoline_stations', function (Blueprint $table) {
            $table->id();
            $table->string('station_name');
            $table->string('address');
            $table->string('barangay')->default('Tankulan');
            $table->string('municipality')->default('Manolo Fortich');
            $table->string('province')->default('Bukidnon');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('contact_number')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasoline_stations');
    }
};
