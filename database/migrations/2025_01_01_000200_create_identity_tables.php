<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// IDENTITY — db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 190);
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password', 255)->nullable();
            $table->string('google_id', 64)->nullable();
            $table->string('phone_e164', 20)->nullable();
            $table->dateTime('phone_verified_at')->nullable();
            $table->string('full_name', 120);
            $table->string('preferred_language', 8)->default('en');
            $table->string('status', 20)->default('active');
            $table->dateTime('last_seen_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique('email', 'uq_users_email');
            $table->unique('phone_e164', 'uq_users_phone');
            $table->unique('google_id', 'uq_users_google');
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_status
            CHECK (status IN ('active','suspended','deleted'))");

        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('type', 30)->default('company');
            $table->string('ursb_number', 60)->nullable();
            $table->string('tin', 30)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        DB::statement("ALTER TABLE organisations ADD CONSTRAINT chk_org_type
            CHECK (type IN ('company','ngo','church','school','government','planner'))");

        Schema::create('organisation_user', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role', 20)->default('member');
            $table->dateTime('created_at')->nullable();

            $table->primary(['organisation_id', 'user_id']);
            $table->index('user_id', 'idx_orguser_user');

            $table->foreign('organisation_id', 'fk_orguser_org')
                ->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('user_id', 'fk_orguser_user')
                ->references('id')->on('users')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE organisation_user ADD CONSTRAINT chk_orguser_role
            CHECK (role IN ('owner','admin','member'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_user');
        Schema::dropIfExists('organisations');
        Schema::dropIfExists('users');
    }
};
