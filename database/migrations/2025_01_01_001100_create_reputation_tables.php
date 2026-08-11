<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// REPUTATION — db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('direction', 25);
            $table->unsignedBigInteger('author_user_id');
            $table->tinyInteger('rating')->unsigned();
            $table->tinyInteger('punctuality')->unsigned()->nullable();
            $table->tinyInteger('quality')->unsigned()->nullable();
            $table->tinyInteger('communication')->unsigned()->nullable();
            $table->tinyInteger('value_rating')->unsigned()->nullable();
            $table->string('comment', 2000)->nullable();
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['booking_id', 'direction'], 'uq_review_direction');
            $table->index(['is_published', 'published_at'], 'idx_reviews_published');

            $table->foreign('booking_id', 'fk_reviews_booking')
                ->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('author_user_id', 'fk_reviews_author')
                ->references('id')->on('users')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE reviews ADD CONSTRAINT chk_reviews_direction
            CHECK (direction IN ('organiser_to_provider','provider_to_organiser'))");
        DB::statement("ALTER TABLE reviews ADD CONSTRAINT chk_reviews_rating
            CHECK (rating BETWEEN 1 AND 5)");
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
