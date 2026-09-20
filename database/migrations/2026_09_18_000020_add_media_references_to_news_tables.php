<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * সংবাদ ↔ মিডিয়া রেফারেন্স (Database-এর Image Reference)।
 *
 * Featured Image এবং গ্যালারির প্রতিটি ছবি এখন `media` টেবিলের রেকর্ডের সাথে
 * যুক্ত থাকে। ফলে:
 *   - সংবাদ এডিট করলে ছবি বদলানো/মুছে ফেলা নিরাপদভাবে কাজ করে
 *   - ছবি মুছে ফেললে (force delete) সংশ্লিষ্ট মিডিয়া রেফারেন্সও পরিষ্কার হয়
 *   - ভবিষ্যতে হোস্টিং বদলালেও পুরোনো রেফারেন্স ভাঙে না
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'featured_media_id')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->foreignId('featured_media_id')->nullable()->after('featured_image')
                    ->constrained('media')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('posts', 'og_media_id')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->foreignId('og_media_id')->nullable()->after('og_image')
                    ->constrained('media')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('post_images', 'media_id')) {
            Schema::table('post_images', function (Blueprint $table) {
                $table->foreignId('media_id')->nullable()->after('post_id')
                    ->constrained('media')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('post_images', 'alt_text')) {
            Schema::table('post_images', function (Blueprint $table) {
                $table->string('alt_text')->nullable()->after('credit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('post_images', 'media_id')) {
            Schema::table('post_images', function (Blueprint $table) {
                $table->dropConstrainedForeignId('media_id');
            });
        }

        if (Schema::hasColumn('post_images', 'alt_text')) {
            Schema::table('post_images', function (Blueprint $table) {
                $table->dropColumn('alt_text');
            });
        }

        foreach (['featured_media_id', 'og_media_id'] as $column) {
            if (Schema::hasColumn('posts', $column)) {
                Schema::table('posts', function (Blueprint $table) use ($column) {
                    $table->dropConstrainedForeignId($column);
                });
            }
        }
    }
};
