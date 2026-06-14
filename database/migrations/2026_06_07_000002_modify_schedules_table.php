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
            // 新增 schedule_type 欄位
            if (!Schema::hasColumn('schedules', 'schedule_type')) {
                $table->enum('schedule_type', ['enroll', 'booking', 'maintenance'])->default('enroll')->after('id');
            }

            // 將 course_id 設為 nullable（enroll 時有值，booking/maintenance 為 null）
            if (Schema::hasColumn('schedules', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->change();
            }

            // 新增 booking_id nullable 並建立外鍵
            if (!Schema::hasColumn('schedules', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('course_id');
                $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            }
        });

        // 嘗試加入 DB-level CHECK constraint（MySQL 8+ 支援）強制互斥規則
        try {
            $constraintName = 'chk_schedules_mutually_exclusive_foreign_keys';
            DB::statement("ALTER TABLE `schedules` ADD CONSTRAINT {$constraintName} CHECK ((schedule_type = 'enroll' AND course_id IS NOT NULL AND booking_id IS NULL) OR (schedule_type IN ('booking','maintenance') AND booking_id IS NOT NULL AND course_id IS NULL))");
        } catch (\Exception $ex) {
            // 若 DB 不支援 CHECK，請依賴應用層驗證與測試來維持互斥性
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
            // 對 course_id 的 change() 回退視情況而定；不在此強制還原
        });

        // 嘗試移除 constraint
        try {
            DB::statement('ALTER TABLE `schedules` DROP CHECK chk_schedules_mutually_exclusive_foreign_keys');
        } catch (\Exception $ex) {
            // ignore
        }
    }
};
