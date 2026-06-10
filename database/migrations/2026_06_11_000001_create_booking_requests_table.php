<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the `booking_requests` table which stores public lab booking requests
     * submitted before any admin approval. This is intentionally separate from the
     * `bookings` table (which holds admin-confirmed records linked to schedules).
     */
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();

            // The lab being requested
            $table->foreignId('lab_id')->constrained('laboratories')->onDelete('cascade');

            // Requester info — no auth required on the public form
            $table->string('requester_name');
            $table->string('requester_email');

            // Booking time window
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');

            // Reason / purpose of the booking
            $table->text('reason');

            // Workflow status: pending → approved or rejected
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Optional rejection message stored for auditing / email content
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
