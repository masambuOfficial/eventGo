<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// NOTIFICATIONS — db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 60);
            $table->json('payload');
            $table->dateTime('read_at')->nullable();
            $table->dateTime('created_at');

            $table->index(['user_id', 'read_at', 'created_at'], 'idx_notif_user_unread');

            $table->foreign('user_id', 'fk_notif_user')
                ->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->string('channel', 15);
            $table->string('status', 15)->default('queued');
            $table->string('provider_ref', 120)->nullable();
            $table->string('idempotency_key', 120);
            $table->bigInteger('cost_ugx')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->string('failed_reason', 255)->nullable();
            $table->dateTime('created_at')->nullable();

            $table->unique('idempotency_key', 'uq_delivery_idem');
            $table->index('notification_id', 'idx_delivery_notification');
            $table->index(['channel', 'status'], 'idx_delivery_channel_status');

            $table->foreign('notification_id', 'fk_delivery_notification')
                ->references('id')->on('notifications')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE notification_deliveries ADD CONSTRAINT chk_delivery_channel
            CHECK (channel IN ('inapp','email','push','sms'))");
        DB::statement("ALTER TABLE notification_deliveries ADD CONSTRAINT chk_delivery_status
            CHECK (status IN ('queued','sent','delivered','failed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notifications');
    }
};
