<?php

use App\Console\Commands\PublishScheduledContent;
use App\Console\Commands\PruneActivityLogs;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// cPanel Cron Job (প্রতি মিনিটে): * * * * * php /home/USER/artisan schedule:run
Schedule::command('mc:publish-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('mc:prune-logs')->dailyAt('03:30');
