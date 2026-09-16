<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            // main|footer|mobile_bottom|top_bar
            $table->string('location', 30)->default('main')->index();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->cascadeOnDelete();
            $table->string('label');
            $table->string('icon', 60)->nullable();
            $table->enum('link_type', ['internal', 'external', 'category', 'page'])->default('internal');
            $table->string('url', 500)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->boolean('open_in_new_tab')->default(false);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            // about|contact|privacy|terms|editorial|advertise|custom
            $table->string('template', 30)->default('default')->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->boolean('show_in_footer')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type', 20)->default('string'); // string|text|bool|json|image
            $table->string('group', 40)->default('general')->index();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action', 60)->index();       // created|updated|deleted|published|login|logout
            $table->string('module', 60)->index();       // news|category|comment|settings...
            $table->string('subject_type', 60)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->index();
            $table->index(['module', 'created_at']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email', 190);
            $table->string('phone', 30)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false)->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 190)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->string('token', 64)->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('news_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone', 30)->nullable();
            $table->string('reporter_email', 190)->nullable();
            $table->string('location')->nullable();
            $table->string('title');
            $table->text('details');
            $table->string('attachment')->nullable();
            $table->enum('status', ['new', 'reviewing', 'accepted', 'rejected'])->default('new')->index();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_reports');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('menus');
    }
};
