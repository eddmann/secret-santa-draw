<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign('groups_owner_id_foreign');
            $table->foreignId('owner_id')->change()->constrained('users')->cascadeOnDelete();
        });

        Schema::table('draws', function (Blueprint $table) {
            $table->dropForeign('draws_group_id_foreign');
            $table->foreignId('group_id')->change()->constrained('groups')->cascadeOnDelete();
        });

        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign('allocations_from_user_id_foreign');
            $table->dropForeign('allocations_to_user_id_foreign');
            $table->foreignId('from_user_id')->change()->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->change()->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign('groups_owner_id_foreign');
            $table->foreignId('owner_id')->change()->constrained('users');
        });

        Schema::table('draws', function (Blueprint $table) {
            $table->dropForeign('draws_group_id_foreign');
            $table->foreignId('group_id')->change()->constrained('groups');
        });

        Schema::table('allocations', function (Blueprint $table) {
            $table->dropForeign('allocations_from_user_id_foreign');
            $table->dropForeign('allocations_to_user_id_foreign');
            $table->foreignId('from_user_id')->change()->nullable()->constrained('users');
            $table->foreignId('to_user_id')->change()->nullable()->constrained('users');
        });
    }
};
