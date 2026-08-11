<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// EVENTS — db/01-schema.sql
// requirements.selected_offer_id and requirements.booking_id are added without
// a foreign key here (offers/bookings don't exist yet); the constraints are
// added in 2025_01_01_001300_add_deferred_foreign_keys.php.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26);
            $table->unsignedBigInteger('owner_user_id');
            $table->unsignedBigInteger('organisation_id')->nullable();
            $table->string('name', 150);
            $table->unsignedBigInteger('event_type_id');
            $table->string('custom_type_label', 80)->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('venue_name', 150)->nullable();
            $table->string('venue_notes', 500)->nullable();
            $table->integer('guest_count_expected')->unsigned()->nullable();
            $table->bigInteger('budget_total_ugx')->nullable();
            $table->text('description')->nullable();
            $table->string('visibility', 10)->default('private');
            $table->string('status', 20)->default('draft');
            $table->tinyInteger('planning_progress')->unsigned()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('public_id', 'uq_events_public');
            $table->index('owner_user_id', 'idx_events_owner');
            $table->index('starts_at', 'idx_events_date');
            $table->index('status', 'idx_events_status');
            $table->index('district_id', 'idx_events_district');

            $table->foreign('owner_user_id', 'fk_events_owner')
                ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organisation_id', 'fk_events_org')
                ->references('id')->on('organisations')->onDelete('set null');
            $table->foreign('event_type_id', 'fk_events_type')
                ->references('id')->on('event_types')->onDelete('restrict');
            $table->foreign('district_id', 'fk_events_district')
                ->references('id')->on('districts')->onDelete('set null');
        });

        DB::statement("ALTER TABLE events ADD CONSTRAINT chk_events_visibility
            CHECK (visibility IN ('private','public'))");
        DB::statement("ALTER TABLE events ADD CONSTRAINT chk_events_status
            CHECK (status IN ('draft','planning','procuring','confirmed','in_progress','completed','cancelled'))");
        DB::statement("ALTER TABLE events ADD CONSTRAINT chk_events_progress
            CHECK (planning_progress BETWEEN 0 AND 100)");
        DB::statement("ALTER TABLE events ADD CONSTRAINT chk_events_dates
            CHECK (ends_at IS NULL OR ends_at >= starts_at)");

        Schema::create('event_scope_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('scope_question_id');
            $table->json('value');
            $table->dateTime('updated_at')->nullable();

            $table->primary(['event_id', 'scope_question_id']);
            $table->index('scope_question_id', 'idx_answers_question');

            $table->foreign('event_id', 'fk_answers_event')
                ->references('id')->on('events')->onDelete('cascade');
            $table->foreign('scope_question_id', 'fk_answers_question')
                ->references('id')->on('scope_questions')->onDelete('cascade');
        });

        Schema::create('event_members', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role', 20)->default('viewer');
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('created_at')->nullable();

            $table->primary(['event_id', 'user_id']);
            $table->index('user_id', 'idx_members_user');

            $table->foreign('event_id', 'fk_members_event')
                ->references('id')->on('events')->onDelete('cascade');
            $table->foreign('user_id', 'fk_members_user')
                ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by', 'fk_members_inviter')
                ->references('id')->on('users')->onDelete('set null');
        });

        DB::statement("ALTER TABLE event_members ADD CONSTRAINT chk_members_role
            CHECK (role IN ('owner','planner','coordinator','viewer'))");

        // The spine. Everything downstream hangs off a requirement. Architecture §4.1.
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26);
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('service_category_id');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('unit', 40)->nullable();
            $table->bigInteger('budget_estimate_ugx')->nullable();
            $table->date('needed_by')->nullable();
            $table->string('priority', 20)->default('important');
            $table->string('status', 25)->default('draft');
            $table->string('source', 15)->default('generated');
            $table->unsignedBigInteger('selected_offer_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('public_id', 'uq_requirements_public');
            $table->index('event_id', 'idx_req_event');
            $table->index('service_category_id', 'idx_req_category');
            $table->index('status', 'idx_req_status');

            $table->foreign('event_id', 'fk_req_event')
                ->references('id')->on('events')->onDelete('cascade');
            $table->foreign('service_category_id', 'fk_req_category')
                ->references('id')->on('service_categories')->onDelete('restrict');
        });

        DB::statement("ALTER TABLE requirements ADD CONSTRAINT chk_req_priority
            CHECK (priority IN ('essential','important','optional'))");
        DB::statement("ALTER TABLE requirements ADD CONSTRAINT chk_req_source
            CHECK (source IN ('generated','manual'))");
        DB::statement("ALTER TABLE requirements ADD CONSTRAINT chk_req_status
            CHECK (status IN ('draft','open','sourcing','offers_received','shortlisted','awarded','booked','fulfilled','no_offers','dropped'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('requirements');
        Schema::dropIfExists('event_members');
        Schema::dropIfExists('event_scope_answers');
        Schema::dropIfExists('events');
    }
};
