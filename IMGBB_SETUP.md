# ImgBB Image Upload System

**API Key:** `4bfac8cf6fa4714236c08292299d2862` (User provided)

## কী করা হয়েছে

### 1. `.env` এ যোগ
```env
IMGBB_API_KEY=4bfac8cf6fa4714236c08292299d2862
IMGBB_ENABLED=true
```

### 2. `config/services.php`
```php
'imgbb' => [
    'key' => env('IMGBB_API_KEY', '4bfac8cf6fa4714236c08292299d2862'),
    'enabled' => env('IMGBB_ENABLED', true),
],
```

### 3. নতুন Service: `app/Services/ImgbbService.php`
- `isEnabled()` - API key আছে কিনা চেক
- `upload(UploadedFile $file)` - ImgBB API তে base64 হিসেবে আপলোড
  - Endpoint: `https://api.imgbb.com/1/upload?key=API_KEY`
  - Response থেকে `display_url`, `thumb`, `width`, `height`, `size` নেয়
- `uploadMany()` - multiple upload

### 4. `MediaUploader` আপডেট
- Constructor এ `ImgbbService` init
- `store()` মেথডে:
  - যদি ছবি হয় + ImgBB enabled + extension pdf না হয় → ImgBB তে আপলোড
  - Success হলে `Media` record এ `disk='imgbb'` এবং `path='https://i.ibb.co/...'` (full URL) save
  - Fail হলে local fallback (public/uploads)
- `delete()` - ImgBB হলে শুধু DB থেকে delete (ImgBB delete API delete_url দিয়ে করতে হয়, TODO)

### 5. `Media` Model
- `getUrlAttribute()` - disk=imgbb হলে path ই URL return করে
- `getIsImgbbAttribute()` - helper

### 6. `MediaController`
- Index এ ImgBB status pass করে
- Stats এ imgbb count যোগ

### 7. `admin/media/index.blade.php`
- উপরে ImgBB সক্রিয়/বন্ধ status banner
- প্রতিটি card এ ImgBB badge
- Stats grid 3 থেকে 4 কলাম (ImgBB count যোগ)

### 8. `SettingController` + `settings/edit.blade.php`
- নতুন group `media`:
  - `imgbb_enabled` (bool)
  - `imgbb_api_key` (text) - hint এ API key দেখানো
  - `imgbb_expiration` (0 = never)

### 9. `SettingSeeder`
- `imgbb_api_key = 4bfac8cf6fa4714236c08292299d2862`
- `imgbb_enabled = 1`

### 10. `composer.json`
- `guzzlehttp/guzzle ^7.8` যোগ (Laravel Http client এর জন্য)

## কীভাবে কাজ করে

1. **Admin → মিডিয়া লাইব্রেরি** থেকে ছবি আপলোড করলে:
   - Validation (MIME, size, PHP tag check)
   - ImgBB enabled হলে → base64 encode → `POST https://api.imgbb.com/1/upload` → response URL → DB তে save
   - ImgBB fail হলে → local `public/uploads/` এ save (fallback)

2. **News → নতুন সংবাদ** এ Featured Image / Gallery আপলোড করলেও একই flow (MediaUploader ব্যবহার করে)

3. **Frontend** এ `mc_image($path)` helper:
   - যদি path `https://` দিয়ে শুরু হয় → যেমন আছে তেমন return (ImgBB URL)
   - না হলে `asset('uploads/...')`

## সুবিধা
- cPanel এ storage limit কম, ImgBB তে unlimited (free tier)
- CDN (i.ibb.co) - দ্রুত লোড
- Direct link - কোথাও embed করা যায়
- Fallback আছে - ImgBB down হলেও local কাজ করবে

## cPanel Deploy
```bash
composer install --no-dev
# .env এ IMGBB_API_KEY সেট আছে কিনা চেক
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
```

## Test
```bash
# Admin login → Media → ছবি আপলোড → উপরে ImgBB সক্রিয় badge দেখাবে
# Upload করার পর card এ ImgBB badge + URL copy করলে https://i.ibb.co/... আসবে
# News create করে Featured Image ImgBB থেকে আসছে কিনা চেক
```

## API Key Security
- `.env` এ রাখা হয়েছে, gitignore এ আছে (`.env` ignore)
- `SettingSeeder` এও রাখা হয়েছে, কিন্তু DB থেকে পরিবর্তন করা যায়
- Public repo তে API key expose না করতে চাইলে `.env.example` এ dummy key রাখুন

## Future Improvements
- ImgBB delete API implement (delete_url ব্যবহার)
- Expiration support (temporary images)
- Bulk delete from ImgBB
- Image resize/compression before upload
