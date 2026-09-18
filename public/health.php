<?php
// Pure PHP health check - no Laravel dependency
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Health Check - মহালছড়ি নিউজ</title>
<style>
body{font-family:sans-serif;background:#f8fafc;color:#1e293b;padding:20px;line-height:1.6}
.ok{color:#16a34a;font-weight:bold}
.fail{color:#dc2626;font-weight:bold}
.card{background:white;border:1px solid #e2e8f0;border-radius:12px;padding:20px;max-width:700px;margin:20px auto}
code{background:#f1f5f9;padding:2px 6px;border-radius:4px}
</style>
</head>
<body>
<div class="card">
<h2>🔍 মহালছড়ি নিউজ - PHP Health Check (Only PHP)</h2>
<?php
$checks = [];

// PHP version
$checks['PHP Version (>=8.2)'] = version_compare(PHP_VERSION, '8.2.0', '>=') ? 'ok' : 'fail';
echo "<p>PHP Version: <code>".PHP_VERSION."</code> - <span class='".$checks['PHP Version (>=8.2)']."'>".$checks['PHP Version (>=8.2)']."</span></p>";

// Extensions
$exts = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'gd', 'curl'];
foreach ($exts as $ext) {
    $ok = extension_loaded($ext) ? 'ok' : 'fail';
    echo "<p>Extension $ext: <span class='$ok'>$ok ".($ok==='ok'?'✅':'❌')."</span></p>";
}

// Vendor
$vendor = file_exists(__DIR__.'/../vendor/autoload.php');
echo "<p>Vendor (composer install): <span class='".($vendor?'ok':'fail')."'>".($vendor?'ok ✅ - Laravel ready':'fail ❌ - Run composer install')."</span></p>";

// .env
$env = file_exists(__DIR__.'/../.env');
echo "<p>.env file: <span class='".($env?'ok':'fail')."'>".($env?'ok ✅':'fail ❌')."</span></p>";
if ($env) {
    $envContent = file_get_contents(__DIR__.'/../.env');
    $hasKey = strpos($envContent, 'APP_KEY=base64:') !== false;
    echo "<p>APP_KEY set: <span class='".($hasKey?'ok':'fail')."'>".($hasKey?'ok ✅':'fail ❌ - Empty')."</span></p>";
    $hasImgbb = strpos($envContent, 'IMGBB_API_KEY=') !== false;
    echo "<p>ImgBB API Key: <span class='".($hasImgbb?'ok':'fail')."'>".($hasImgbb?'ok ✅ - 4bfac8cf...':'fail ❌')."</span></p>";
}

// Storage writable
$writable = is_writable(__DIR__.'/../storage');
echo "<p>Storage writable: <span class='".($writable?'ok':'fail')."'>".($writable?'ok ✅':'fail ❌ - chmod 775 storage')."</span></p>";

$cacheWritable = is_writable(__DIR__.'/../bootstrap/cache');
echo "<p>Bootstrap/cache writable: <span class='".($cacheWritable?'ok':'fail')."'>".($cacheWritable?'ok ✅':'fail ❌ - chmod 775 bootstrap/cache')."</span></p>";

// Public css
$css = file_exists(__DIR__.'/css/app.css');
echo "<p>public/css/app.css (no npm needed): <span class='".($css?'ok':'fail')."'>".($css?'ok ✅ - Tailwind built':'fail ❌')."</span></p>";

// Uploads
$uploads = is_dir(__DIR__.'/uploads') && is_writable(__DIR__.'/uploads');
echo "<p>public/uploads writable: <span class='".($uploads?'ok':'fail')."'>".($uploads?'ok ✅':'fail ❌')."</span></p>";

echo "<hr>";
echo "<h3>📋 Only PHP Deployment (cPanel) Steps:</h3>";
echo "<pre>
1. Upload all files to cPanel (except node_modules)
2. Create MySQL DB and set .env DB_*
3. cPanel Terminal:
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   chmod -R 775 storage bootstrap/cache public/uploads

4. Set document_root to public/ (cPanel → Domains)
5. Visit /health.php to check
6. Admin: /admin/login (admin@mahalcharinews.com / ChangeMe@123)

No Node.js needed! public/css/app.css already built.
ImgBB: Images will upload to i.ibb.co using API key 4bfac8cf...
</pre>";

echo "<p><a href='/'>← Home</a> | <a href='/admin/login'>Admin Login</a> | <a href='https://www.facebook.com/profile.php?id=100068836585906' target='_blank'>Facebook Page</a></p>";
?>
</div>
</body>
</html>
