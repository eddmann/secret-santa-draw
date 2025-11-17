<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing text data to JSONB array format
        DB::statement("
            ALTER TABLE allocations
            ALTER COLUMN from_ideas TYPE jsonb
            USING CASE
                WHEN from_ideas IS NULL OR from_ideas = '' THEN '[]'::jsonb
                ELSE jsonb_build_array(from_ideas)
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert JSONB array back to text (first element or empty string)
        DB::statement("
            ALTER TABLE allocations
            ALTER COLUMN from_ideas TYPE text
            USING CASE
                WHEN jsonb_array_length(from_ideas) > 0 THEN from_ideas->>0
                ELSE ''
            END
        ");
    }
};
