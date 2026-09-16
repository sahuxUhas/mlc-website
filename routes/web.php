<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\PublicSite;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| দৈনিক মহালছড়ি নিউজ — রাউট ম্যাপ
| ডেমোর hash-ভিত্তিক URL গুলো আসল SEO-friendly সার্ভার রাউটে রূপান্তরিত।
|--------------------------------------------------------------------------
*/

Route::middleware(['mc.publish'])->group(function () {

    /* ===== পাবলিক ওয়েবসাইট ===== */
    Route::get('/', [PublicSite\HomeController::class, 'index'])->name('home');

    // সর্বশেষ সংবাদ
    Route::get('/latest-news', [PublicSite\LatestNewsController::class, 'index'])->name('latest');

    // ক্যাটাগরি
    Route::get('/category/{slug}', [PublicSite\CategoryController::class, 'show'])->name('category.show');

    // ফুল নিউজ / আর্টিকেল (legacy /article/{id} থেকে রিডাইরেক্ট সহ)
    Route::get('/news/{slug}', [PublicSite\NewsController::class, 'show'])
        ->middleware('mc.trackview')->name('news.show');
    Route::get('/article/{id}', [PublicSite\NewsController::class, 'legacyRedirect']);

    // নিউজ পেজ থেকে মন্তব্য রিপোর্ট (spam/abuse)
    Route::post('/news/{slug}/report/{comment}', [PublicSite\NewsController::class, 'report'])
        ->middleware('throttle:mc_comment')->name('news.report');

    // ট্যাগ
    Route::get('/tag/{slug}', [PublicSite\TagController::class, 'show'])->name('tag.show');

    // ভিডিও
    Route::get('/videos', [PublicSite\VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/{slug}', [PublicSite\VideoController::class, 'show'])
        ->middleware('mc.trackview:video')->name('videos.show');

    // ঘোষণা
    Route::get('/announcements', [PublicSite\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{slug}', [PublicSite\AnnouncementController::class, 'show'])->name('announcements.show');

    // ফটো গ্যালারি / অ্যালবাম
    Route::get('/photo-gallery', [PublicSite\GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/photo-gallery/{slug}', [PublicSite\GalleryController::class, 'show'])->name('gallery.show');

    // সার্চ
    Route::get('/search', [PublicSite\SearchController::class, 'index'])->name('search');
    Route::get('/search/suggest', [PublicSite\SearchController::class, 'suggest'])->name('search.suggest');

    // ই-পেপার
    Route::get('/epaper', [PublicSite\EpaperController::class, 'index'])->name('epaper.index');
    Route::get('/epaper/{id}/download', [PublicSite\EpaperController::class, 'download'])->name('epaper.download');

    // রিপোর্টার পরিচিতি
    Route::get('/reporters', [PublicSite\ReporterController::class, 'index'])->name('reporters.index');
    Route::get('/reporters/{slug}', [PublicSite\ReporterController::class, 'show'])->name('reporters.show');

    // পাঠকের মন্তব্য (অ্যাকাউন্ট ছাড়াই Name + Comment)
    Route::post('/comments', [PublicSite\CommentController::class, 'store'])
        ->middleware('throttle:mc_comment')->name('comments.store');
    Route::post('/comments/{comment}/report', [PublicSite\CommentController::class, 'report'])
        ->middleware('throttle:mc_comment')->name('comments.report');

    // যোগাযোগ / প্রতিবেদন জমা / নিউজলেটার
    Route::post('/contact', [PublicSite\ContactController::class, 'store'])
        ->middleware('throttle:10,1')->name('contact.store');
    Route::post('/submit-report', [PublicSite\ReportSubmitController::class, 'store'])
        ->middleware('throttle:5,1')->name('report.store');
    Route::post('/newsletter/subscribe', [PublicSite\NewsletterController::class, 'subscribe'])
        ->middleware('throttle:10,1')->name('newsletter.subscribe');

    // বিজ্ঞাপন ক্লিক ট্র্যাকিং
    Route::get('/go/ad/{ad}', [PublicSite\AdClickController::class, 'redirect'])->name('ad.click');

    // SEO: sitemap + robots
    Route::get('/sitemap.xml', [PublicSite\SitemapController::class, 'index'])->name('sitemap');
    Route::get('/robots.txt', [PublicSite\SitemapController::class, 'robots'])->name('robots');
    Route::get('/feed', [PublicSite\SitemapController::class, 'feed'])->name('feed');

    // নির্দিষ্ট স্ট্যাটিক পেজ (about / contact / advertise ইত্যাদি)
    Route::get('/about', [PublicSite\PageController::class, 'about'])->name('about');
    Route::get('/contact', [PublicSite\PageController::class, 'contact'])->name('contact');
    Route::get('/advertise', [PublicSite\PageController::class, 'advertise'])->name('advertise');
    Route::get('/submit-report', [PublicSite\PageController::class, 'submitReport'])->name('submit-report');

});

/*
|--------------------------------------------------------------------------
| অ্যাডমিন প্যানেল
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // অথেনটিকেশন
    Route::middleware('guest.admin')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])
            ->middleware('throttle:mc_login')->name('login.attempt');
    });

    Route::middleware('admin')->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // প্রোফাইল
        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [Admin\ProfileController::class, 'password'])->name('profile.password');

        // সংবাদ ব্যবস্থাপনা
        Route::middleware('permission:news.view')->group(function () {
            Route::get('news', [Admin\NewsController::class, 'index'])->name('news.index');
            Route::get('news/create', [Admin\NewsController::class, 'create'])
                ->middleware('permission:news.create')->name('news.create');
            Route::post('news', [Admin\NewsController::class, 'store'])
                ->middleware('permission:news.create')->name('news.store');
            Route::get('news/{post}/edit', [Admin\NewsController::class, 'edit'])->name('news.edit');
            Route::put('news/{post}', [Admin\NewsController::class, 'update'])->name('news.update');
            Route::post('news/{post}/status/{status}', [Admin\NewsController::class, 'changeStatus'])->name('news.status');
            Route::post('news/bulk', [Admin\NewsController::class, 'bulk'])->name('news.bulk');
            Route::post('news/{post}/images/reorder', [Admin\NewsController::class, 'reorderImages'])->name('news.images.reorder');
            Route::post('news/{post}/featured', [Admin\NewsController::class, 'setFeaturedImage'])->name('news.featured');
            Route::post('news/{post}/images', [Admin\NewsController::class, 'addImages'])->name('news.images.store');
            Route::delete('news/{post}/images/{image}', [Admin\NewsController::class, 'destroyImage'])->name('news.images.destroy');
        });
        Route::delete('news/{post}', [Admin\NewsController::class, 'destroy'])
            ->middleware('permission:news.delete')->name('news.destroy');

        // ক্যাটাগরি ও সাব-ক্যাটাগরি
        Route::middleware('permission:categories.manage')->group(function () {
            Route::get('categories', [Admin\CategoryController::class, 'index'])->name('categories.index');
            Route::post('categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}', [Admin\CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
            Route::post('categories/reorder', [Admin\CategoryController::class, 'reorder'])->name('categories.reorder');
            Route::post('categories/{category}/toggle', [Admin\CategoryController::class, 'toggle'])->name('categories.toggle');
        });

        // ট্যাগ
        Route::middleware('permission:tags.manage')->group(function () {
            Route::get('tags', [Admin\TagController::class, 'index'])->name('tags.index');
            Route::delete('tags/{tag}', [Admin\TagController::class, 'destroy'])->name('tags.destroy');
        });

        // রিপোর্টার ও লেখক
        Route::middleware('permission:reporters.manage')->group(function () {
            Route::get('reporters', [Admin\ReporterController::class, 'index'])->name('reporters.index');
            Route::post('reporters', [Admin\ReporterController::class, 'store'])->name('reporters.store');
            Route::put('reporters/{reporter}', [Admin\ReporterController::class, 'update'])->name('reporters.update');
            Route::delete('reporters/{reporter}', [Admin\ReporterController::class, 'destroy'])->name('reporters.destroy');
        });

        // মিডিয়া লাইব্রেরি
        Route::get('media', [Admin\MediaController::class, 'index'])->middleware('permission:media.upload')->name('media.index');
        Route::post('media', [Admin\MediaController::class, 'store'])->middleware('permission:media.upload')->name('media.store');
        Route::put('media/{media}', [Admin\MediaController::class, 'update'])->middleware('permission:media.upload')->name('media.update');
        Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->middleware('permission:media.delete')->name('media.destroy');

        // ব্রেকিং নিউজ
        Route::middleware('permission:breaking.manage')->group(function () {
            Route::get('breaking', [Admin\BreakingNewsController::class, 'index'])->name('breaking.index');
            Route::post('breaking', [Admin\BreakingNewsController::class, 'store'])->name('breaking.store');
            Route::put('breaking/{breaking}', [Admin\BreakingNewsController::class, 'update'])->name('breaking.update');
            Route::delete('breaking/{breaking}', [Admin\BreakingNewsController::class, 'destroy'])->name('breaking.destroy');
            Route::post('breaking/{breaking}/toggle', [Admin\BreakingNewsController::class, 'toggle'])->name('breaking.toggle');
        });

        // ভিডিও
        Route::middleware('permission:videos.manage')->group(function () {
            Route::get('videos', [Admin\VideoController::class, 'index'])->name('videos.index');
            Route::post('videos', [Admin\VideoController::class, 'store'])->name('videos.store');
            Route::put('videos/{video}', [Admin\VideoController::class, 'update'])->name('videos.update');
            Route::delete('videos/{video}', [Admin\VideoController::class, 'destroy'])->name('videos.destroy');
            Route::post('videos/{video}/restore', [Admin\VideoController::class, 'restore'])->name('videos.restore');
        });

        // ঘোষণা
        Route::middleware('permission:announcements.manage')->group(function () {
            Route::get('announcements', [Admin\AnnouncementController::class, 'index'])->name('announcements.index');
            Route::post('announcements', [Admin\AnnouncementController::class, 'store'])->name('announcements.store');
            Route::put('announcements/{announcement}', [Admin\AnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('announcements/{announcement}', [Admin\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
            Route::post('announcements/{announcement}/restore', [Admin\AnnouncementController::class, 'restore'])->name('announcements.restore');
        });

        // ফটো গ্যালারি / অ্যালবাম
        Route::middleware('permission:albums.manage')->group(function () {
            Route::get('albums', [Admin\AlbumController::class, 'index'])->name('albums.index');
            Route::post('albums', [Admin\AlbumController::class, 'store'])->name('albums.store');
            Route::get('albums/{album}/edit', [Admin\AlbumController::class, 'edit'])->name('albums.edit');
            Route::put('albums/{album}', [Admin\AlbumController::class, 'update'])->name('albums.update');
            Route::delete('albums/{album}', [Admin\AlbumController::class, 'destroy'])->name('albums.destroy');
            Route::post('albums/{album}/photos', [Admin\AlbumController::class, 'addPhotos'])->name('albums.photos.store');
            Route::post('albums/{album}/photos/reorder', [Admin\AlbumController::class, 'reorderPhotos'])->name('albums.photos.reorder');
            Route::delete('albums/{album}/photos/{photo}', [Admin\AlbumController::class, 'destroyPhoto'])->name('albums.photos.destroy');
        });

        // মন্তব্য মডারেশন
        Route::middleware('permission:comments.moderate')->group(function () {
            Route::get('comments', [Admin\CommentController::class, 'index'])->name('comments.index');
            Route::post('comments/{comment}/status/{status}', [Admin\CommentController::class, 'updateStatus'])->name('comments.status');
            Route::delete('comments/{comment}', [Admin\CommentController::class, 'destroy'])->name('comments.destroy');
            Route::post('comments/bulk', [Admin\CommentController::class, 'bulk'])->name('comments.bulk');
        });

        // বিজ্ঞাপন ম্যানেজার
        Route::middleware('permission:ads.manage')->group(function () {
            Route::get('ads', [Admin\AdvertisementController::class, 'index'])->name('ads.index');
            Route::post('ads', [Admin\AdvertisementController::class, 'store'])->name('ads.store');
            Route::put('ads/{advertisement}', [Admin\AdvertisementController::class, 'update'])->name('ads.update');
            Route::delete('ads/{advertisement}', [Admin\AdvertisementController::class, 'destroy'])->name('ads.destroy');
            Route::post('ads/{advertisement}/toggle', [Admin\AdvertisementController::class, 'toggle'])->name('ads.toggle');
        });

        // মেনু ম্যানেজার
        Route::middleware('permission:menus.manage')->group(function () {
            Route::get('menus', [Admin\MenuController::class, 'index'])->name('menus.index');
            Route::post('menus', [Admin\MenuController::class, 'store'])->name('menus.store');
            Route::put('menus/{menu}', [Admin\MenuController::class, 'update'])->name('menus.update');
            Route::delete('menus/{menu}', [Admin\MenuController::class, 'destroy'])->name('menus.destroy');
            Route::post('menus/reorder', [Admin\MenuController::class, 'reorder'])->name('menus.reorder');
        });

        // স্ট্যাটিক পেজ
        Route::middleware('permission:pages.manage')->group(function () {
            Route::get('pages', [Admin\PageController::class, 'index'])->name('pages.index');
            Route::get('pages/create', [Admin\PageController::class, 'create'])->name('pages.create');
            Route::post('pages', [Admin\PageController::class, 'store'])->name('pages.store');
            Route::get('pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
            Route::put('pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
            Route::delete('pages/{page}', [Admin\PageController::class, 'destroy'])->name('pages.destroy');
        });

        // ই-পেপার
        Route::middleware('permission:epapers.manage')->group(function () {
            Route::get('epapers', [Admin\EpaperController::class, 'index'])->name('epapers.index');
            Route::post('epapers', [Admin\EpaperController::class, 'store'])->name('epapers.store');
            Route::delete('epapers/{epaper}', [Admin\EpaperController::class, 'destroy'])->name('epapers.destroy');
        });

        // সাইট সেটিংস + SEO
        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
            Route::get('seo', [Admin\SeoController::class, 'edit'])->name('seo.edit');
            Route::put('seo', [Admin\SeoController::class, 'update'])->name('seo.update');
        });

        // ইউজার ও রোল (শুধু সুপার অ্যাডমিন)
        Route::middleware('role:super_admin')->group(function () {
            Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
            Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
            Route::put('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
        });

        // রিসাইকল বিন / ট্র্যাশ
        Route::middleware('permission:news.delete')->group(function () {
            Route::get('trash', [Admin\TrashController::class, 'index'])->name('trash.index');
            Route::post('trash/{type}/{id}/restore', [Admin\TrashController::class, 'restore'])->name('trash.restore');
            Route::delete('trash/{type}/{id}', [Admin\TrashController::class, 'forceDelete'])->name('trash.force-delete');
            Route::delete('trash/{type}', [Admin\TrashController::class, 'emptyTrash'])->name('trash.empty');
        });

        // অ্যাক্টিভিটি লগ
        Route::middleware('permission:activity.view')->group(function () {
            Route::get('activity', [Admin\ActivityController::class, 'index'])->name('activity.index');
            Route::delete('activity', [Admin\ActivityController::class, 'clear'])->name('activity.clear');
        });

        // পাঠকের প্রতিবেদন
        Route::middleware('permission:reports.manage')->group(function () {
            Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
            Route::put('reports/{report}', [Admin\ReportController::class, 'update'])->name('reports.update');
            Route::delete('reports/{report}', [Admin\ReportController::class, 'destroy'])->name('reports.destroy');
        });

        // যোগাযোগ বার্তা
        Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

        // নিউজলেটার
        Route::get('newsletter', [Admin\NewsletterController::class, 'index'])->name('newsletter.index');
        Route::delete('newsletter/{subscriber}', [Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| ডাইনামিক স্ট্যাটিক পেজ — ফাইলের একদম শেষে রাখা হয়েছে
| যাতে /admin, /news, /videos প্রভৃতি রাউট কখনো ঢাকা না পড়ে।
| সংরক্ষিত শব্দগুলো বাদ দেওয়া হয়েছে।
|--------------------------------------------------------------------------
*/
Route::get('/{slug}', [PublicSite\PageController::class, 'show'])
    ->where('slug', '(?!admin|login|logout|up|storage|telescope|horizon)[A-Za-z0-9\-_\x{0980}-\x{09FF}]+')
    ->name('page.show');
