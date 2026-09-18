<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Check if vendor exists - common cPanel deployment issue
if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
    http_response_code(500);
    echo <<<HTML
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ডিপ্লয়মেন্ট বাকি - দৈনিক মহালছড়ি নিউজ</title>
<style>
body{font-family:'Noto Sans Bengali',sans-serif;background:#0D1422;color:#F1F5F9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
.card{max-width:640px;background:#182233;border:1px solid #263246;border-radius:16px;padding:28px}
h1{color:#E21D2B;font-size:22px;margin:0 0 12px}
code{background:#0D1422;border:1px solid #263246;padding:2px 6px;border-radius:4px;font-size:13px}
pre{background:#0D1422;border:1px solid #263246;padding:12px;border-radius:8px;overflow:auto;font-size:12px;line-height:1.5}
a{color:#22C55E}
</style>
</head>
<body>
<div class="card">
<h1>⚠️ Vendor ফোল্ডার পাওয়া যায়নি</h1>
<p>এটি cPanel এ Laravel deploy করার সময় সাধারণ সমস্যা। <code>composer install</code> চালাতে হবে।</p>
<h3>cPanel Terminal থেকে চালান:</h3>
<pre>cd ~/mahalcharinews.com
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
chmod -R 775 storage bootstrap/cache</pre>
<h3>যদি composer না থাকে:</h3>
<p>cPanel → Select PHP Version → Composer enable করুন, অথবা SSH থেকে composer install করুন।</p>
<p>বিস্তারিত: <code>README.md</code> দেখুন</p>
<p><strong>শুধু PHP লাগবে</strong> - Node.js/npm লাগবে না, <code>public/css/app.css</code> আগে থেকেই build করা আছে।</p>
</div>
</body>
</html>
HTML;
    exit;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
