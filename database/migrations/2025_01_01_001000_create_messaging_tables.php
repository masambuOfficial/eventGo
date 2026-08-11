<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// MESSAGING — db/01-schema.sql
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type', 30);
            $table->unsignedBigInteger('subject_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['subject_type', 'subject_id'], 'uq_thread_subject');
        });

        Schema::create('thread_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role', 15)->default('member');
            $table->dateTime('last_read_at')->nullable();

            $table->primary(['thread_id', 'user_id']);
            $table->index('user_id', 'idx_tp_user');

            $table->foreign('thread_id', 'fk_tp_thread')
                ->references('id')->on('threads')->onDelete('cascade');
            $table->foreign('user_id', 'fk_tp_user')
                ->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('sender_user_id');
            $table->text('body');
            // Flag, do not block: false positives cost more than leakage. §11.2.
            $table->boolean('contains_contact_info')->default(false);
            $table->dateTime('created_at');

            $table->index(['thread_id', 'created_at'], 'idx_messages_thread');

            $table->foreign('thread_id', 'fk_messages_thread')
                ->references('id')->on('threads')->onDelete('cascade');
            $table->foreign('sender_user_id', 'fk_messages_sender')
                ->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->string('path', 255);
            $table->string('mime', 100)->nullable();
            $table->bigInteger('size_bytes')->unsigned()->nullable();

            $table->index('message_id', 'idx_msg_att_message');

            $table->foreign('message_id', 'fk_msg_att_message')
                ->references('id')->on('messages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('thread_participants');
        Schema::dropIfExists('threads');
    }
};
