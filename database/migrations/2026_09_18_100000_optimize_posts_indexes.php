<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🚀 Performance Optimization Migration
 * 
 * যোগ করা হচ্ছে:
 * - Scheduled posts এর জন্য composite index
 * - Popular posts sorting এর জন্য composite index
 * 
 * চালাতে: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Scheduled posts auto-publish query optimization
            $table->index(['status', 'scheduled_at'], 'idx_posts_scheduled');
            
            // Popular/Most viewed posts query optimization
            $table->index(['status', 'views'], 'idx_posts_popular');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('idx_posts_scheduled');
            $table->dropIndex('idx_posts_popular');
        });
    }
};
