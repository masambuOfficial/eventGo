<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

// DEFERRED FOREIGN KEYS — circular references resolved after all tables
// exist. Mirrors the ALTER TABLE section at the bottom of db/01-schema.sql.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requirements', function ($table) {
            $table->foreign('selected_offer_id', 'fk_req_selected_offer')
                ->references('id')->on('offers')->onDelete('set null');
            $table->foreign('booking_id', 'fk_req_booking')
                ->references('id')->on('bookings')->onDelete('set null');
        });

        Schema::table('providers', function ($table) {
            $table->foreign('plan_id', 'fk_providers_plan')
                ->references('id')->on('plans')->onDelete('set null');
            $table->foreign('referred_by_staff_id', 'fk_providers_referrer')
                ->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function ($table) {
            $table->dropForeign('fk_providers_referrer');
            $table->dropForeign('fk_providers_plan');
        });

        Schema::table('requirements', function ($table) {
            $table->dropForeign('fk_req_booking');
            $table->dropForeign('fk_req_selected_offer');
        });
    }
};
