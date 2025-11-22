<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign('allocations_draw_id_foreign');
            $table->foreignId('draw_id')->change()->constrained('draws')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign('allocations_draw_id_foreign');
            $table->foreignId('draw_id')->change()->constrained('draws');
        });
    }
};
