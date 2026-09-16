<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

/** পুরোনো অ্যাক্টিভিটি লগ মুছে ডাটাবেস হালকা রাখে */
class PruneActivityLogs extends Command
{
    protected $signature = 'mc:prune-logs {--days=180 : কত দিনের লগ রাখা হবে}';

    protected $description = 'নির্দিষ্ট সময়ের চেয়ে পুরোনো অ্যাক্টিভিটি লগ মুছে ফেলুন';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("মুছে ফেলা লগ: {$deleted}");

        return self::SUCCESS;
    }
}
