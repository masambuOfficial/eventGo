<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// PROVIDERS — db/01-schema.sql
// providers.plan_id and providers.referred_by_staff_id are added without a
// foreign key here (plans doesn't exist yet); the constraints are added in
// 2025_01_01_001300_add_deferred_foreign_keys.php, mirroring the source SQL's
// deferred ALTER TABLE section.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->string('business_name', 150);
            // lowercased, punctuation and suffixes stripped. Duplicate detection
            // leans on this because MySQL has no pg_trgm. See architecture §11.3.
            $table->string('normalised_name', 150);
            $table->string('slug', 170);
            $table->text('about')->nullable();
            $table->string('primary_phone_e164', 20)->nullable();
            $table->string('whatsapp_phone', 20)->nullable();
            $table->string('ursb_number', 60)->nullable();
            $table->string('tin', 30)->nullable();
            $table->unsignedBigInteger('base_district_id')->nullable();
            $table->tinyInteger('verification_tier')->unsigned()->default(0);
            $table->tinyInteger('profile_completeness')->unsigned()->default(0);
            $table->string('onboarding_channel', 20)->default('self_serve');
            $table->unsignedBigInteger('referred_by_staff_id')->nullable();
            $table->decimal('rating_avg', 3, 2)->nullable();
            $table->integer('rating_count')->unsigned()->default(0);
            $table->integer('bookings_won')->unsigned()->default(0);
            $table->decimal('response_rate', 5, 2)->nullable();
            $table->integer('median_response_minutes')->unsigned()->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->dateTime('plan_expires_at')->nullable();
            $table->dateTime('featured_until')->nullable();
            $table->string('payout_msisdn', 20)->nullable();
            $table->string('payout_registered_name', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('slug', 'uq_providers_slug');
            $table->unique('owner_user_id', 'uq_providers_owner');
            $table->index('base_district_id', 'idx_providers_district');
            $table->index('normalised_name', 'idx_providers_norm_name');
            $table->index(['verification_tier', 'is_active'], 'idx_providers_tier_active');
            $table->index('payout_msisdn', 'idx_providers_payout');
            $table->fullText(['business_name', 'about'], 'ft_providers');

            $table->foreign('owner_user_id', 'fk_providers_owner')
                ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('base_district_id', 'fk_providers_district')
                ->references('id')->on('districts')->onDelete('set null');
        });

        DB::statement("ALTER TABLE providers ADD CONSTRAINT chk_providers_tier
            CHECK (verification_tier BETWEEN 0 AND 3)");
        DB::statement("ALTER TABLE providers ADD CONSTRAINT chk_providers_completeness
            CHECK (profile_completeness BETWEEN 0 AND 100)");
        DB::statement("ALTER TABLE providers ADD CONSTRAINT chk_providers_channel
            CHECK (onboarding_channel IN ('self_serve','assisted'))");
        DB::statement("ALTER TABLE providers ADD CONSTRAINT chk_providers_rating
            CHECK (rating_avg IS NULL OR (rating_avg >= 1 AND rating_avg <= 5))");

        Schema::create('provider_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->string('platform', 20);
            $table->string('handle', 120);
            $table->string('profile_url', 255);
            $table->integer('follower_count')->unsigned()->nullable();
            $table->date('page_created_at')->nullable();
            $table->json('raw_snapshot')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['platform', 'handle'], 'uq_social_platform_handle');
            $table->index('provider_id', 'idx_social_provider');

            $table->foreign('provider_id', 'fk_social_provider')
                ->references('id')->on('providers')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE provider_social_accounts ADD CONSTRAINT chk_social_platform
            CHECK (platform IN ('facebook','instagram','tiktok','x','linkedin'))");

        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('service_category_id');
            $table->integer('min_capacity')->unsigned()->nullable();
            $table->integer('max_capacity')->unsigned()->nullable();
            $table->bigInteger('price_min_ugx')->nullable();
            $table->bigInteger('price_max_ugx')->nullable();
            $table->string('price_unit', 40)->nullable();
            $table->smallInteger('lead_time_days')->unsigned()->nullable();
            $table->string('description', 500)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['provider_id', 'service_category_id'], 'uq_provider_service');
            $table->index('service_category_id', 'idx_psvc_category');

            $table->foreign('provider_id', 'fk_psvc_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('service_category_id', 'fk_psvc_category')
                ->references('id')->on('service_categories')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE provider_services ADD CONSTRAINT chk_psvc_capacity
            CHECK (min_capacity IS NULL OR max_capacity IS NULL OR min_capacity <= max_capacity)");
        DB::statement("ALTER TABLE provider_services ADD CONSTRAINT chk_psvc_price
            CHECK (price_min_ugx IS NULL OR price_max_ugx IS NULL OR price_min_ugx <= price_max_ugx)");

        Schema::create('provider_service_areas', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('district_id');

            $table->primary(['provider_id', 'district_id']);
            $table->index('district_id', 'idx_psa_district');

            $table->foreign('provider_id', 'fk_psa_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('district_id', 'fk_psa_district')
                ->references('id')->on('districts')->onDelete('cascade');
        });

        // Capacity per date, not a boolean. A caterer can serve three weddings on
        // a Saturday; a venue can serve one. Architecture §4.3.
        Schema::create('provider_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->date('date');
            $table->smallInteger('capacity_total')->unsigned()->default(1);
            $table->smallInteger('capacity_used')->unsigned()->default(0);
            $table->boolean('is_blackout')->default(false);
            $table->string('note', 200)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['provider_id', 'date'], 'uq_avail');
            $table->index('date', 'idx_avail_date');

            $table->foreign('provider_id', 'fk_avail_provider')
                ->references('id')->on('providers')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE provider_availability ADD CONSTRAINT chk_avail_capacity
            CHECK (capacity_used <= capacity_total)");

        Schema::create('provider_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->string('type', 15)->default('image');
            $table->string('path', 255);
            $table->json('variants')->nullable();
            $table->string('caption', 200)->nullable();
            $table->date('event_date')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index(['provider_id', 'sort_order'], 'idx_media_provider');

            $table->foreign('provider_id', 'fk_media_provider')
                ->references('id')->on('providers')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE provider_media ADD CONSTRAINT chk_media_type
            CHECK (type IN ('image','video'))");

        Schema::create('provider_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->tinyInteger('tier')->unsigned();
            $table->string('evidence_type', 30);
            $table->string('evidence_path', 255)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('notes', 500)->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index('provider_id', 'idx_verif_provider');
            $table->index(['status', 'created_at'], 'idx_verif_status');

            $table->foreign('provider_id', 'fk_verif_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('reviewed_by', 'fk_verif_reviewer')
                ->references('id')->on('users')->onDelete('set null');
        });

        DB::statement("ALTER TABLE provider_verifications ADD CONSTRAINT chk_verif_status
            CHECK (status IN ('pending','approved','rejected'))");
        DB::statement("ALTER TABLE provider_verifications ADD CONSTRAINT chk_verif_evidence
            CHECK (evidence_type IN ('social_page','ursb','tin','national_id','reference','site_visit'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_verifications');
        Schema::dropIfExists('provider_media');
        Schema::dropIfExists('provider_availability');
        Schema::dropIfExists('provider_service_areas');
        Schema::dropIfExists('provider_services');
        Schema::dropIfExists('provider_social_accounts');
        Schema::dropIfExists('providers');
    }
};
