<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('video_url', 500)->nullable();      // Facebook / YouTube / mp4
            $table->string('embed_type', 20)->default('facebook'); // facebook|youtube|file
            $table->string('thumbnail')->nullable();
            $table->text('description')->nullable();
            $table->string('duration', 20)->nullable();
            $table->boolean('is_reel')->default(false);
            $table->enum('status', ['draft', 'scheduled', 'published', 'archived'])->default('draft')->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->string('link', 500)->nullable();
            $table->string('link_text')->nullable();
            // notice|warning|event|job|general
            $table->enum('type', ['notice', 'warning', 'event', 'job', 'general'])->default('general')->index();
            $table->unsignedInteger('priority')->default(0)->index();
            $table->enum('status', ['draft', 'published', 'expired'])->default('draft')->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('breaking_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('url', 500)->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('priority')->default(0)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email', 190)->nullable();
            $table->text('body');
            // pending|approved|rejected|spam
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending')->index();
            $table->unsignedTinyInteger('report_count')->default(0);
            $table->boolean('is_reported')->default(false)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['commentable_type', 'commentable_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('breaking_news');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('videos');
    }
};
