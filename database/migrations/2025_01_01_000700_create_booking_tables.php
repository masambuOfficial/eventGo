<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// BOOKINGS — light shared workspace, no payment gates, no adjudication.
// db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26);
            $table->unsignedBigInteger('requirement_id');
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('event_id');
            $table->bigInteger('agreed_total_ugx');
            $table->bigInteger('original_total_ugx');
            $table->string('status', 20)->default('confirmed');
            $table->dateTime('contacts_released_at')->nullable();
            $table->dateTime('organiser_completed_at')->nullable();
            $table->dateTime('provider_completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancelled_by_side', 12)->nullable();
            $table->string('cancellation_note', 500)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('public_id', 'uq_bookings_public');
            $table->unique('requirement_id', 'uq_bookings_requirement');
            $table->index(['provider_id', 'status'], 'idx_bookings_provider');
            $table->index('event_id', 'idx_bookings_event');

            $table->foreign('requirement_id', 'fk_bookings_requirement')
                ->references('id')->on('requirements')->onDelete('cascade');
            $table->foreign('offer_id', 'fk_bookings_offer')
                ->references('id')->on('offers')->onDelete('restrict');
            $table->foreign('provider_id', 'fk_bookings_provider')
                ->references('id')->on('providers')->onDelete('restrict');
            $table->foreign('event_id', 'fk_bookings_event')
                ->references('id')->on('events')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_bookings_status
            CHECK (status IN ('confirmed','in_progress','completed','closed','cancelled'))");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_bookings_side
            CHECK (cancelled_by_side IS NULL OR cancelled_by_side IN ('organiser','provider'))");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_bookings_totals
            CHECK (agreed_total_ugx >= 0 AND original_total_ugx >= 0)");

        Schema::create('booking_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('title', 200);
            $table->string('description', 1000)->nullable();
            $table->string('owner_side', 12);
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->string('status', 15)->default('open');
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by_user_id')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index(['booking_id', 'sort_order'], 'idx_btasks_booking');
            $table->index(['due_at', 'status'], 'idx_btasks_due');

            $table->foreign('booking_id', 'fk_btasks_booking')
                ->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('assigned_user_id', 'fk_btasks_assignee')
                ->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_by_user_id', 'fk_btasks_completer')
                ->references('id')->on('users')->onDelete('set null');
        });

        DB::statement("ALTER TABLE booking_tasks ADD CONSTRAINT chk_btasks_side
            CHECK (owner_side IN ('organiser','provider','both'))");
        DB::statement("ALTER TABLE booking_tasks ADD CONSTRAINT chk_btasks_status
            CHECK (status IN ('open','done','cancelled'))");

        Schema::create('booking_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->string('label', 150);
            $table->string('path', 255);
            $table->string('mime', 100)->nullable();
            $table->bigInteger('size_bytes')->unsigned()->nullable();
            $table->dateTime('created_at')->nullable();

            $table->index('booking_id', 'idx_bfiles_booking');

            $table->foreign('booking_id', 'fk_bfiles_booking')
                ->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('uploaded_by_user_id', 'fk_bfiles_uploader')
                ->references('id')->on('users')->onDelete('cascade');
        });

        // Append-only log of what the parties say they agreed. No approval state
        // machine: Event Go records, it does not enforce. Architecture §5.4.
        Schema::create('booking_amendments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('changed_by_user_id');
            $table->bigInteger('previous_total_ugx');
            $table->bigInteger('new_total_ugx');
            $table->string('note', 500)->nullable();
            $table->dateTime('created_at')->nullable();

            $table->index(['booking_id', 'created_at'], 'idx_bamend_booking');

            $table->foreign('booking_id', 'fk_bamend_booking')
                ->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('changed_by_user_id', 'fk_bamend_user')
                ->references('id')->on('users')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE booking_amendments ADD CONSTRAINT chk_bamend_totals
            CHECK (previous_total_ugx >= 0 AND new_total_ugx >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_amendments');
        Schema::dropIfExists('booking_files');
        Schema::dropIfExists('booking_tasks');
        Schema::dropIfExists('bookings');
    }
};
