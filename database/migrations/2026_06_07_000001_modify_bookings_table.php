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

            if (!Schema::hasColumn('bookings', 'type')) {
                $table->enum('type', ['booking', 'maintenance'])->default('booking')->after('id');
            }

            if (Schema::hasColumn('bookings', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            }

            if (!Schema::hasColumn('bookings', 'booker_name')) {
                $table->string('booker_name')->nullable()->after('user_id');
            }

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

        });
    }
};
