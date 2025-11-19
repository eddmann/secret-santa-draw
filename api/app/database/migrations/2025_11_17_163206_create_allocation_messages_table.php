<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_id')->constrained('allocations')->cascadeOnDelete();
            $table->boolean('is_from_secret_santa');
            $table->text('message');
            $table->timestamps(precision: 6);

            $table->index('allocation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_messages');
    }
};
