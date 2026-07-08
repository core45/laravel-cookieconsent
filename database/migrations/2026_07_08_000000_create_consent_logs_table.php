<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();
            $table->string('consent_id', 64)->index();
            $table->string('user_type')->nullable();

            match (config('cookieconsent.logging.morph_id_type', 'int')) {
                'uuid' => $table->uuid('user_id')->nullable(),
                'ulid' => $table->ulid('user_id')->nullable(),
                'string' => $table->string('user_id')->nullable(),
                default => $table->unsignedBigInteger('user_id')->nullable(),
            };

            $table->index(['user_type', 'user_id']);
            $table->string('action', 20);
            $table->string('accept_type', 20);
            $table->json('accepted_categories');
            $table->json('rejected_categories');
            $table->json('accepted_services')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->string('policy_version')->nullable();
            $table->string('policy_hash', 64)->nullable();
            $table->string('language_code', 12)->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload');
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
