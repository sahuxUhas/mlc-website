<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ==========================================================================
 * পুরোনো Fake/Demo View Count পরিষ্কার
 * ==========================================================================
 * Seeder/ডেমো ডেটায় সংবাদ ও ভিডিওতে বসানো ভুয়া পঠনসংখ্যা (যেমন ১৮৪২০,
 * ২২৪০০) শূন্য করা হয়। এরপর থেকে প্রতিটি সংখ্যা শুধুমাত্র বাস্তব ভিজিট
 * থেকে তৈরি হবে — নতুন সংবাদ প্রকাশিত হয় ০ ভিউ নিয়ে।
 *
 * দ্রষ্টব্য: এটি একবারই চলে (ডেটা মাইগ্রেশন)। ভবিষ্যতের ভিউ অক্ষত থাকে।
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['posts', 'videos'] as $table) {
            try {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'views')) {
                    DB::table($table)->update(['views' => 0]);
                }
            } catch (\Throwable $e) {
                // কোনো কারণে ব্যর্থ হলেও migration আটকে যাবে না
            }
        }
    }

    public function down(): void
    {
        // ইচ্ছাকৃতভাবে কিছুই ফেরানো হয় না — ভুয়া সংখ্যা ফিরিয়ে আনার দরকার নেই।
    }
};
