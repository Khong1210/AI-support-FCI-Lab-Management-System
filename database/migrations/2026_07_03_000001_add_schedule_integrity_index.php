<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite index to reinforce schedule integrity at the DB level.
     *
     * While MySQL does not support exclusion constraints natively (unlike
     * PostgreSQL), this composite index on (semester_id, lab_id, day_of_week,
     * start_time, end_time) serves two purposes:
     *
     *   1. Accelerates the conflict-detection queries that our collision shield
     *      runs before every insert/update.
     *   2. Acts as a fast lookup for any manual audit query to detect overlaps
     *      that may have slipped through (defense in depth).
     *
     * Overlap detection still relies on application-level logic (strict
     * interval-overlap comparison) because MySQL cannot enforce "no
     * overlapping time ranges" at the schema level.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(
                ['semester_id', 'lab_id', 'day_of_week', 'start_time', 'end_time'],
                'idx_schedules_collision_lookup'
            );
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_collision_lookup');
        });
    }
};