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
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('featured_image')->nullable();
            $table->string('image_caption')->nullable();
            $table->string('image_credit')->nullable();
            $table->text('excerpt')->nullable();            // short description
            $table->longText('content')->nullable();        // full news (HTML)
            $table->string('video_url', 500)->nullable();
            $table->string('location')->nullable();

            // draft|pending|published|scheduled|archived
            $table->enum('status', ['draft', 'pending', 'published', 'scheduled', 'archived'])->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_breaking')->default(false)->index();
            $table->boolean('allow_comments')->default(true);

            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable()->index();

            $table->unsignedBigInteger('views')->default(0)->index();
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // পারফরম্যান্স ইনডেক্স (হোম/ক্যাটাগরি/সর্বশেষ কুয়েরি)
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status', 'published_at']);
            $table->index(['is_breaking', 'published_at']);
            $table->fullText(['title', 'excerpt', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
