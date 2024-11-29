<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draw_id')->constrained('draws')->cascadeOnDelete();
            $table->string('from_name');
            $table->string('from_email');
            $table->foreignId('from_user_id')->nullable()->constrained('users');
            $table->string('from_access_token');
            $table->text('from_ideas');
            $table->string('to_email');
            $table->foreignId('to_user_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['draw_id', 'from_email', 'to_email'], 'draw_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
