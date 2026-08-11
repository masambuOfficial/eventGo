<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// SOURCING — db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requirement_id');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('closes_at')->nullable();
            $table->boolean('budget_visible')->default(false);
            $table->bigInteger('budget_min_ugx')->nullable();
            $table->bigInteger('budget_max_ugx')->nullable();
            $table->integer('view_count')->unsigned()->default(0);
            $table->integer('offer_count')->unsigned()->default(0);
            $table->string('status', 15)->default('open');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('requirement_id', 'uq_opp_requirement');
            $table->index(['status', 'closes_at'], 'idx_opp_status_closes');

            $table->foreign('requirement_id', 'fk_opp_requirement')
                ->references('id')->on('requirements')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE opportunities ADD CONSTRAINT chk_opp_status
            CHECK (status IN ('open','closed','cancelled'))");
        DB::statement("ALTER TABLE opportunities ADD CONSTRAINT chk_opp_budget
            CHECK (budget_min_ugx IS NULL OR budget_max_ugx IS NULL OR budget_min_ugx <= budget_max_ugx)");

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requirement_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('invited_by_user_id');
            $table->string('message', 1000)->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->string('status', 15)->default('sent');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['requirement_id', 'provider_id'], 'uq_invitation');
            $table->index(['provider_id', 'status'], 'idx_inv_provider');

            $table->foreign('requirement_id', 'fk_inv_requirement')
                ->references('id')->on('requirements')->onDelete('cascade');
            $table->foreign('provider_id', 'fk_inv_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('invited_by_user_id', 'fk_inv_inviter')
                ->references('id')->on('users')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE invitations ADD CONSTRAINT chk_inv_status
            CHECK (status IN ('sent','viewed','responded','declined','expired'))");

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requirement_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('submitted_by_user_id');
            $table->bigInteger('total_ugx');
            $table->string('scope_summary', 1000)->nullable();
            $table->text('inclusions')->nullable();
            $table->text('exclusions')->nullable();
            // The PROVIDER's terms. Event Go publishes none of its own. Architecture §4.3.
            $table->text('terms')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('availability_confirmed')->default(false);
            $table->string('status', 15)->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('withdrawn_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->foreign('requirement_id', 'fk_offers_requirement')
                ->references('id')->on('requirements')->onDelete('cascade');
            $table->foreign('provider_id', 'fk_offers_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('submitted_by_user_id', 'fk_offers_submitter')
                ->references('id')->on('users')->onDelete('cascade');

            $table->index(['provider_id', 'status'], 'idx_offers_provider');
            $table->index('status', 'idx_offers_status');
            $table->unique(['requirement_id', 'provider_id'], 'uq_offer_per_provider');
        });

        DB::statement("ALTER TABLE offers ADD CONSTRAINT chk_offers_total CHECK (total_ugx >= 0)");
        DB::statement("ALTER TABLE offers ADD CONSTRAINT chk_offers_status
            CHECK (status IN ('draft','submitted','under_review','shortlisted','accepted','rejected','withdrawn','expired'))");

        // MySQL has no partial indexes. A STORED generated column that is NULL
        // unless accepted, combined with a unique key, gives the same guarantee:
        // unique indexes ignore NULLs, so at most one accepted offer per requirement.
        // Do not remove this — it is the only thing enforcing that invariant.
        DB::statement("ALTER TABLE offers ADD COLUMN accepted_marker TINYINT UNSIGNED
            GENERATED ALWAYS AS (CASE WHEN status = 'accepted' THEN 1 ELSE NULL END) STORED");
        DB::statement("ALTER TABLE offers ADD CONSTRAINT uq_offer_one_accepted
            UNIQUE (requirement_id, accepted_marker)");

        // Line items are what make offers comparable. Architecture §4.3.
        Schema::create('offer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->string('description', 255);
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 40)->nullable();
            $table->bigInteger('unit_price_ugx');
            $table->bigInteger('line_total_ugx');
            $table->smallInteger('sort_order')->default(0);

            $table->index(['offer_id', 'sort_order'], 'idx_offer_items_offer');

            $table->foreign('offer_id', 'fk_offer_items_offer')
                ->references('id')->on('offers')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE offer_items ADD CONSTRAINT chk_offer_items_amounts
            CHECK (unit_price_ugx >= 0 AND line_total_ugx >= 0 AND quantity > 0)");

        Schema::create('clarifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requirement_id');
            $table->unsignedBigInteger('offer_id')->nullable();
            $table->unsignedBigInteger('asked_by_user_id');
            $table->string('question', 1000);
            $table->string('answer', 1000)->nullable();
            $table->unsignedBigInteger('answered_by_user_id')->nullable();
            $table->dateTime('answered_at')->nullable();
            $table->boolean('is_public')->default(true);
            $table->dateTime('created_at')->nullable();

            $table->index('requirement_id', 'idx_clar_requirement');

            $table->foreign('requirement_id', 'fk_clar_requirement')
                ->references('id')->on('requirements')->onDelete('cascade');
            $table->foreign('offer_id', 'fk_clar_offer')
                ->references('id')->on('offers')->onDelete('cascade');
            $table->foreign('asked_by_user_id', 'fk_clar_asker')
                ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('answered_by_user_id', 'fk_clar_answerer')
                ->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('shortlist_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('requirement_id');
            $table->unsignedBigInteger('offer_id');
            $table->smallInteger('rank_order')->default(0);
            $table->string('note', 500)->nullable();
            $table->dateTime('created_at')->nullable();

            $table->primary(['requirement_id', 'offer_id']);
            $table->index('offer_id', 'idx_shortlist_offer');

            $table->foreign('requirement_id', 'fk_shortlist_requirement')
                ->references('id')->on('requirements')->onDelete('cascade');
            $table->foreign('offer_id', 'fk_shortlist_offer')
                ->references('id')->on('offers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shortlist_entries');
        Schema::dropIfExists('clarifications');
        Schema::dropIfExists('offer_items');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('opportunities');
    }
};
