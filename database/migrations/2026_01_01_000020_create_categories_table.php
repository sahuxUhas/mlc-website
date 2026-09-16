<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 60)->nullable();          // phosphor icon class
            $table->string('color', 20)->nullable();
            $table->string('union_name')->nullable();       // এলাকা/ইউনিয়ন
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_visible')->default(true)->index();
            $table->boolean('show_on_home')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->unsignedInteger('sort_order')->default(0)->index();
            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->unsignedInteger('posts_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
