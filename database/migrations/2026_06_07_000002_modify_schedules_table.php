<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {

            if (!Schema::hasColumn('schedules', 'schedule_type')) {
                $table->enum('schedule_type', ['enroll', 'booking', 'maintenance'])->default('enroll')->after('id');
            }

            if (Schema::hasColumn('schedules', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->change();
            }

            if (!Schema::hasColumn('schedules', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('course_id');
                $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            }
        });

        try {
            $constraintName = 'chk_schedules_mutually_exclusive_foreign_keys';
            DB::statement("ALTER TABLE `schedules` ADD CONSTRAINT {$constraintName} CHECK ((schedule_type = 'enroll' AND course_id IS NOT NULL AND booking_id IS NULL) OR (schedule_type IN ('booking','maintenance') AND booking_id IS NOT NULL AND course_id IS NULL))");
        } catch (\Exception $ex) {

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'booking_id')) {
                $table->dropForeign([ 'booking_id' ]);
                $table->dropColumn('booking_id');
            }
            if (Schema::hasColumn('schedules', 'schedule_type')) {
                $table->dropColumn('schedule_type');
            }

        });

        try {
            DB::statement('ALTER TABLE `schedules` DROP CHECK chk_schedules_mutually_exclusive_foreign_keys');
        } catch (\Exception $ex) {
            // ignore
        }
    }
};
