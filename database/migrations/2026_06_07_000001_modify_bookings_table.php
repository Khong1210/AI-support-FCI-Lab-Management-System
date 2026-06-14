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
        Schema::table('bookings', function (Blueprint $table) {
            // 新增 type 欄位，用於區分 booking / maintenance
            if (!Schema::hasColumn('bookings', 'type')) {
                $table->enum('type', ['booking', 'maintenance'])->default('booking')->after('id');
            }

            // 允許 user_id 為 nullable（維護時為派工技師，或未註冊預約者為 null）
            if (Schema::hasColumn('bookings', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            }

            // 新增 booker_name，管理者可輸入外來訪客姓名
            if (!Schema::hasColumn('bookings', 'booker_name')) {
                $table->string('booker_name')->nullable()->after('user_id');
            }

            // 注意：不變動現有 status 欄位型態（若你想將 status 改為文字，請先確認所有程式碼相容）
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('bookings', 'booker_name')) {
                $table->dropColumn('booker_name');
            }
            // 恢復 user_id null 行為視 DB 狀態而定，這裡不嘗試重置為 not null
        });
    }
};
