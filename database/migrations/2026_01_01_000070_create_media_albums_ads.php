<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // মিডিয়া লাইব্রেরি
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('disk', 20)->default('uploads');
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->string('extension', 12)->nullable();
            $table->unsignedBigInteger('size')->default(0);      // bytes
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            // usage tracking
            $table->string('used_in', 60)->nullable();
            $table->unsignedBigInteger('used_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['extension', 'created_at']);
            // FULLTEXT ইনডেক্স শুধু MySQL/MariaDB এ সমর্থিত
            // (SQLite এ RuntimeException এড়াতে ড্রাইভার যাচাই করা হয়েছে —
            //  ফলে টেস্টিং ও লোকাল ডেভ দুটোতেই migration চলে)
            if (Schema::getConnection()->getDriverName() === 'mysql') {
            $table->fullText(['file_name', 'alt_text', 'caption']);
            }
        });

        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedInteger('photos_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('album_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // top_header|homepage|article|sidebar|footer|in_content|after_first_paragraph
            $table->string('position', 60)->index();
            $table->enum('type', ['image', 'html'])->default('image');
            $table->string('image')->nullable();
            $table->text('html_code')->nullable();
            $table->string('link', 500)->nullable();
            $table->string('link_target', 20)->default('_blank');
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('priority')->default(0)->index();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->date('starts_at')->nullable()->index();
            $table->date('ends_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // ই-পেপার
        Schema::create('epapers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('issue_date')->index();
            $table->string('file_path');
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('pages')->default(0);
            $table->string('size_label', 20)->nullable();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epapers');
        Schema::dropIfExists('advertisements');
        Schema::dropIfExists('album_photos');
        Schema::dropIfExists('albums');
        Schema::dropIfExists('media');
    }
};
