<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups');
            $table->year('year');
            $table->text('description');
            $table->timestamps();

            $table->unique(['group_id', 'year'], 'group_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draws');
    }
};
