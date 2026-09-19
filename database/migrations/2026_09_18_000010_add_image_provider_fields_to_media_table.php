<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Image Hosting (ImgBB) রেফারেন্স সংরক্ষণের কলাম যোগ করা হয় —
 * MySQL-এ ছবি BLOB হিসেবে নয়, শুধু URL/ID রেফারেন্স থাকে।
 *
 *  provider            : imgbb | local
 *  provider_id         : হোস্টিংয়ের ছবির আইডি
 *  provider_url        : হোস্টিংয়ের মূল URL (শুধু Backend — UI-তে কখনো দেখানো হয় না)
 *  provider_delete_url : ছবি মোছার হোস্টিং URL (শুধু Backend)
 *  thumb_url           : ছোট প্রিভিউ URL (Backend)
 *  folder              : মিডিয়া লাইব্রেরির ফোল্ডার (আগে কলামটিই ছিল না)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'provider')) {
                $table->string('provider', 30)->default('local')->index()->after('disk');
            }
            if (! Schema::hasColumn('media', 'provider_id')) {
                $table->string('provider_id', 120)->nullable()->after('provider');
            }
            if (! Schema::hasColumn('media', 'provider_url')) {
                $table->text('provider_url')->nullable()->after('path');
            }
            if (! Schema::hasColumn('media', 'provider_delete_url')) {
                $table->text('provider_delete_url')->nullable()->after('provider_url');
            }
            if (! Schema::hasColumn('media', 'thumb_url')) {
                $table->text('thumb_url')->nullable()->after('provider_delete_url');
            }
            if (! Schema::hasColumn('media', 'folder')) {
                $table->string('folder', 60)->nullable()->index()->after('extension');
            }
        });

        // আগের সংস্করণে ImgBB তে যাওয়া ছবিগুলোর provider ঠিকভাবে চিহ্নিত করা
        try {
            DB::table('media')
                ->where(function ($q) {
                    $q->where('disk', 'imgbb')->orWhere('path', 'like', 'http%');
                })
                ->where(fn ($q) => $q->whereNull('provider')->orWhere('provider', 'local'))
                ->update(['provider' => 'imgbb', 'disk' => 'imgbb']);
        } catch (\Throwable $e) {
            // ব্যাকফিল ব্যর্থ হলেও migration বন্ধ হবে না
        }
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            foreach (['provider_delete_url', 'thumb_url', 'provider_url', 'provider_id', 'provider', 'folder'] as $column) {
                if (Schema::hasColumn('media', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
