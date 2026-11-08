<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();
            // Nullable so failed-login attempts against a non-existent email can still be logged.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email_attempted')->nullable(); // captured for failed logins even if user_id is null
            $table->string('role')->nullable();
            $table->enum('action', ['login', 'logout', 'failed_login', 'account_settings_changed', 'important_action']);
            $table->string('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};
