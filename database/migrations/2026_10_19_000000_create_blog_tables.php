<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 190)->unique();
            $table->string('type', 30)->index();
            $table->string('title', 200);
            $table->string('excerpt', 300)->nullable();
            $table->text('body');
            $table->string('image_path', 255)->nullable();
            $table->string('image_alt', 200)->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('path', 190)->unique();
            $table->string('name', 190);
            $table->string('mime', 60)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt', 300)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60);
            $table->string('subject', 200)->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('media');
        Schema::dropIfExists('posts');
    }
};
