<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_types', function (Blueprint $table) {
            $table->string('specification')->nullable()->after('name'); // e.g. "95 Octane", "10% Ethanol Blend"
            $table->text('description')->nullable()->after('specification');
            $table->foreignId('created_by')->nullable()->after('description')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fuel_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['specification', 'description']);
        });
    }
};
