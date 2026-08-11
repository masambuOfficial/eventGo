<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ATTRIBUTION — the revenue mechanism, proves provider ROI. Architecture §8.3.
// db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_impressions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->string('context', 20);
            $table->unsignedBigInteger('requirement_id')->nullable();
            $table->unsignedBigInteger('viewer_user_id')->nullable();
            $table->boolean('was_featured')->default(false);
            $table->dateTime('created_at');

            $table->index(['provider_id', 'created_at'], 'idx_impr_provider_date');

            $table->foreign('provider_id', 'fk_impr_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('requirement_id', 'fk_impr_requirement')
                ->references('id')->on('requirements')->onDelete('set null');
            $table->foreign('viewer_user_id', 'fk_impr_viewer')
                ->references('id')->on('users')->onDelete('set null');
        });

        DB::statement("ALTER TABLE provider_impressions ADD CONSTRAINT chk_impr_context
            CHECK (context IN ('search','category','profile','opportunity','featured'))");

        // Who is connected to whom, and who is working with whom.
        //
        // Observing the relationship graph is the product; governing the relationship
        // is not. Event Go records that these two parties connected and roughly what
        // came of it. It does not touch their terms.
        //
        // Partly derivable from bookings, deliberately not left that way:
        //   * a connection can exist with NO booking (they talked, then went offline).
        //     That case is invisible in bookings and is the best leakage signal we get.
        //   * the "my network" screens need this without aggregating bookings each time.
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organiser_user_id');
            $table->unsignedBigInteger('provider_id');
            $table->string('origin', 25);
            $table->unsignedBigInteger('first_event_id')->nullable();
            $table->dateTime('first_connected_at');
            $table->dateTime('last_interaction_at')->nullable();
            $table->integer('bookings_count')->unsigned()->default(0);
            $table->bigInteger('bookings_value_ugx')->default(0);
            $table->string('status', 15)->default('contacted');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['organiser_user_id', 'provider_id'], 'uq_connection');
            $table->index(['provider_id', 'status', 'last_interaction_at'], 'idx_conn_provider');
            $table->index(['organiser_user_id', 'status'], 'idx_conn_organiser');

            $table->foreign('organiser_user_id', 'fk_conn_organiser')
                ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id', 'fk_conn_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('first_event_id', 'fk_conn_event')
                ->references('id')->on('events')->onDelete('set null');
        });

        DB::statement("ALTER TABLE connections ADD CONSTRAINT chk_conn_origin
            CHECK (origin IN ('opportunity_match','direct_invitation','search','featured'))");
        // contacted = they spoke, no booking yet.  working = at least one live booking.
        // past = booked before, nothing active now.
        DB::statement("ALTER TABLE connections ADD CONSTRAINT chk_conn_status
            CHECK (status IN ('contacted','working','past'))");
        DB::statement("ALTER TABLE connections ADD CONSTRAINT chk_conn_totals
            CHECK (bookings_value_ugx >= 0)");

        Schema::create('provider_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('requirement_id');
            $table->string('source', 25);
            $table->dateTime('notified_at')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->dateTime('offered_at')->nullable();
            $table->string('outcome', 15)->default('pending');
            $table->dateTime('outcome_at')->nullable();
            $table->bigInteger('value_ugx')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['provider_id', 'requirement_id'], 'uq_lead');
            $table->index(['provider_id', 'outcome', 'created_at'], 'idx_leads_provider_outcome');

            $table->foreign('provider_id', 'fk_leads_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('requirement_id', 'fk_leads_requirement')
                ->references('id')->on('requirements')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE provider_leads ADD CONSTRAINT chk_leads_source
            CHECK (source IN ('opportunity_match','direct_invitation','search','featured'))");
        DB::statement("ALTER TABLE provider_leads ADD CONSTRAINT chk_leads_outcome
            CHECK (outcome IN ('pending','no_bid','lost','won','expired'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_leads');
        Schema::dropIfExists('connections');
        Schema::dropIfExists('provider_impressions');
    }
};
