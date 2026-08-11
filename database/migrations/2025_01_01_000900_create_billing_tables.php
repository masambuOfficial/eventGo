<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// BILLING — prepaid packages over mobile money. Architecture §8.2.
// db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40);
            $table->string('name', 80);
            $table->string('audience', 15)->default('provider');
            $table->bigInteger('price_ugx');
            $table->smallInteger('duration_days')->unsigned();
            $table->json('entitlements');
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('code', 'uq_plans_code');
        });

        DB::statement("ALTER TABLE plans ADD CONSTRAINT chk_plans_audience
            CHECK (audience IN ('provider','organiser'))");
        DB::statement("ALTER TABLE plans ADD CONSTRAINT chk_plans_price
            CHECK (price_ugx >= 0 AND duration_days > 0)");

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscriber_type', 20);
            $table->unsignedBigInteger('subscriber_id');
            $table->unsignedBigInteger('plan_id');
            $table->dateTime('starts_at');
            $table->dateTime('expires_at');
            $table->string('status', 15)->default('pending');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index(['subscriber_type', 'subscriber_id', 'status'], 'idx_subs_subscriber');
            $table->index(['expires_at', 'status'], 'idx_subs_expiry');

            $table->foreign('plan_id', 'fk_subs_plan')
                ->references('id')->on('plans')->onDelete('restrict');
        });

        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT chk_subs_status
            CHECK (status IN ('pending','active','expired','cancelled'))");
        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT chk_subs_dates
            CHECK (expires_at > starts_at)");

        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->bigInteger('amount_ugx');
            $table->string('channel', 20);
            $table->string('gateway', 30);
            $table->string('gateway_ref', 120);
            $table->string('payer_msisdn', 20)->nullable();
            $table->string('payer_name', 150)->nullable();
            $table->string('status', 15)->default('pending');
            $table->json('raw_callback')->nullable();
            $table->dateTime('initiated_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            // Idempotency: gateways resend webhooks. Architecture §9.3.
            $table->unique(['gateway', 'gateway_ref'], 'uq_billing_gateway_ref');
            $table->index('subscription_id', 'idx_billing_subscription');
            $table->index(['status', 'created_at'], 'idx_billing_status');

            $table->foreign('subscription_id', 'fk_billing_subscription')
                ->references('id')->on('subscriptions')->onDelete('set null');
        });

        DB::statement("ALTER TABLE billing_payments ADD CONSTRAINT chk_billing_status
            CHECK (status IN ('pending','settled','failed','refunded'))");
        DB::statement("ALTER TABLE billing_payments ADD CONSTRAINT chk_billing_channel
            CHECK (channel IN ('mtn_momo','airtel_money','card','bank','cash','manual'))");
        DB::statement("ALTER TABLE billing_payments ADD CONSTRAINT chk_billing_amount
            CHECK (amount_ugx >= 0)");

        Schema::create('featured_placements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->bigInteger('price_ugx');
            $table->integer('impressions')->unsigned()->default(0);
            $table->integer('clicks')->unsigned()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->index(['starts_at', 'ends_at'], 'idx_featured_window');
            $table->index(['service_category_id', 'district_id', 'starts_at', 'ends_at'], 'idx_featured_slot');

            $table->foreign('provider_id', 'fk_featured_provider')
                ->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('service_category_id', 'fk_featured_category')
                ->references('id')->on('service_categories')->onDelete('cascade');
            $table->foreign('district_id', 'fk_featured_district')
                ->references('id')->on('districts')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE featured_placements ADD CONSTRAINT chk_featured_window
            CHECK (ends_at > starts_at)");
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_placements');
        Schema::dropIfExists('billing_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
