<?php

namespace App\Providers;

use App\Models\Post;
use App\Observers\PostObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // cPanel এ HTTPS চালু থাকলে mixed-content এড়াতে
        if (env('APP_ENV') === 'production' && env('MC_FORCE_HTTPS', true)) {
            URL::forceScheme('https');
        }

        // cPanel সাব-ফোল্ডারে ডিপ্লয় করলে asset URL ঠিক রাখা
        if ($base = env('MC_ASSET_BASE')) {
            URL::forceRootUrl(rtrim((string) $base, '/'));
        }

        Model::preventLazyLoading(false);
        Model::unguard(false);
        Schema::defaultStringLength(191);
        Paginator::useTailwind();

        // 🚀 Observer Registration — পোস্ট পরিবর্তনে auto cache clear
        Post::observe(PostObserver::class);

        /**
         * Blade ডিরেক্টিভ — বিজ্ঞাপন স্লট
         * ব্যবহার: @ad('top_header')
         */
        Blade::directive('ad', function (string $position) {
            return "<?php echo view('partials.ad', ['position' => {$position}])->render(); ?>";
        });

        /**
         * Blade ডিরেক্টিভ — সক্রিয় নেভিগেশন হাইলাইট
         * ব্যবহার: @isactive('news.*') bg-red-600 @endisactive
         */
        Blade::if('isactive', function (string ...$patterns) {
            return request()->routeIs(...$patterns);
        });
    }
}
