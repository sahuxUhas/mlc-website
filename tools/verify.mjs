#!/usr/bin/env node
/**
 * স্ট্যাটিক ভেরিফায়ার — PHP/Laravel রানটাইম ছাড়াই রিপো যাচাই।
 * (স্যান্ডবক্সে PHP/composer নেই, তাই artisan test চালানো যায় না —
 *  এই স্ক্রিপ্টটি কাঠামোগত ভুল ধরে: Blade ডিরেক্টিভ, ব্রেস, কম্পোনেন্ট, ভিউ, রাউট, সেটিংস কী, CSS ক্লাস)
 *
 * ব্যবহার: node tools/verify.mjs
 */
import fs from 'node:fs';
import path from 'node:path';

const ROOT = path.resolve(import.meta.dirname, '..');
const problems = [];
const notes = [];

function walk(dir, ext, out = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (['node_modules', '.git', 'vendor', 'storage', 'legacy-demo', 'bootstrap/cache'].includes(entry.name)) continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full, ext, out);
    else if (ext.some((e) => entry.name.endsWith(e))) out.push(full);
  }
  return out;
}
const rel = (p) => path.relative(ROOT, p);
const read = (p) => fs.readFileSync(p, 'utf8');

/* ---------------------------------------------------------------- 1. Blade ডিরেক্টিভ ব্যালান্স */
const PAIRS = [
  ['@if', '@endif'],
  ['@unless', '@endunless'],
  ['@foreach', '@endforeach'],
  ['@forelse', '@endforelse'],
  ['@for', '@endfor'],
  ['@while', '@endwhile'],
  ['@push', '@endpush'],
  ['@prepend', '@endprepend'],
  ['@isset', '@endisset'],
  ['@auth', '@endauth'],
  ['@guest', '@endguest'],
  ['@once', '@endonce'],
  ['@switch', '@endswitch'],
];

/**
 * ডিরেক্টিভ গণনা — সামনে \w বা @ থাকলে ধরা হবে না (@endif@if কে দুটোই গোনা হয়),
 * পেছনে শব্দ-অক্ষর থাকলেও না (@sectional বাদ)।
 */
function countDirective(src, directive) {
  // (?![\w-]) থাকায় @section/@sectional আলাদা হয়; lookbehind বাদ দিতে হয়
  // কারণ Blade এ @endif@if এর মতো ডিরেক্টিভ পাশাপাশি বসে।
  const re = new RegExp(`${directive}(?![\\w-])`, 'g');
  return (src.match(re) || []).length;
}

const bladeFiles = walk(ROOT, ['.blade.php']);
for (const file of bladeFiles) {
  const src = read(file);
  // Blade কমেন্ট বাদ — {{-- --}} (লাইন কমেন্ট {{-- দিয়ে শুরু হয়ে লাইন শেষে শেষ হতে পারে)
  const scan = src.replace(/\{\{--[\s\S]*?--\}\}/g, '')
                  .replace(/^[ \t]*\{\{--.*$/gm, '');

  for (const [open, close] of PAIRS) {
    const opens = countDirective(scan, open);
    const closes = countDirective(scan, close);
    if (opens !== closes) {
      problems.push(`${rel(file)}: ${open} (${opens}) ও ${close} (${closes}) সমান নয়`);
    }
  }

  // @empty শুধু @forelse এর ভেতরে চলে, তাই আলাদাভাবে গোনা হয় না
  const forelse = countDirective(scan, '@forelse');
  const endForelse = countDirective(scan, '@endforelse');
  if (forelse !== endForelse) problems.push(`${rel(file)}: @forelse (${forelse}) ≠ @endforelse (${endForelse})`);

  // ব্লক ফর্মের @section('x') ... @endsection — ইনলাইন @section('title','মান') বাদ
  const blockSections = (scan.match(/@section\(\s*['"][^'"]+['"]\s*\)(?!\s*,)/g) || []).length;
  const endSections = countDirective(scan, '@endsection') + countDirective(scan, '@stop') + countDirective(scan, '@show');
  if (blockSections > endSections) {
    problems.push(`${rel(file)}: ব্লক @section (${blockSections}) > @endsection/@stop/@show (${endSections})`);
  }
}
notes.push(`Blade ডিরেক্টিভ ব্যালান্স: ${bladeFiles.length}টি ফাইল যাচাই`);

/* ---------------------------------------------------------------- 2. PHP ব্রেস/বন্ধনী ব্যালান্স (স্ট্রিং-সচেতন) */
function stripPhpLiterals(code) {
  let out = '';
  let i = 0;
  while (i < code.length) {
    const ch = code[i];
    const two = code.slice(i, i + 2);
    if (two === '//') { while (i < code.length && code[i] !== '\n') i++; continue; }
    if (two === '/*') { i += 2; while (i < code.length && code.slice(i, i + 2) !== '*/') i++; i += 2; continue; }
    if (two === '#[' || ch === '#') {
      // PHP attribute নয়, সাধারণ # (CSS/HTML) হতে পারে — শুধু লাইন কমেন্ট ধরা হয় PHP অংশে
      if (ch === '#' && code.slice(i, i + 2) !== '#[') { while (i < code.length && code[i] !== '\n') i++; continue; }
    }
    if (ch === "'" || ch === '"') {
      const quote = ch;
      i++;
      while (i < code.length) {
        if (code[i] === '\\') { i += 2; continue; }
        if (code[i] === quote) { i++; break; }
        i++;
      }
      out += '""';
      continue;
    }
    out += ch;
    i++;
  }
  return out;
}

const phpFiles = walk(path.join(ROOT, 'app'), ['.php']).concat(walk(path.join(ROOT, 'database'), ['.php']), walk(path.join(ROOT, 'tests'), ['.php']), walk(path.join(ROOT, 'routes'), ['.php']));
for (const file of phpFiles) {
  const clean = stripPhpLiterals(read(file));
  for (const [o, c, name] of [['{', '}', 'brace'], ['(', ')', 'paren'], ['[', ']', 'bracket']]) {
    const opens = (clean.match(new RegExp('\\' + o, 'g')) || []).length;
    const closes = (clean.match(new RegExp('\\' + c, 'g')) || []).length;
    if (opens !== closes) problems.push(`${rel(file)}: ${name} সমান নয় (${opens} ${o} বনাম ${closes} ${c})`);
  }
  // কোট জোড়
  const singles = (clean.match(/'/g) || []).length;
  const doubles = (clean.match(/"/g) || []).length;
  if (singles % 2 !== 0) problems.push(`${rel(file)}: একক কোট (' ) জোড় নয়`);
  if (doubles % 2 !== 0) problems.push(`${rel(file)}: ডাবল কোট (") জোড় নয়`);
}
notes.push(`PHP ব্রেস/কোট ব্যালান্স: ${phpFiles.length}টি ফাইল যাচাই`);

/* ---------------------------------------------------------------- 3. Blade কম্পোনেন্ট ও ইনক্লুড অস্তিত্ব */
const viewPath = (name) => path.join(ROOT, 'resources/views', name.replace(/\./g, '/') + '.blade.php');
const componentPath = (name) => {
  const parts = name.split('.');
  return path.join(ROOT, 'resources/views/components', parts.join('/') + '.blade.php');
};

for (const file of bladeFiles) {
  const src = read(file);
  for (const m of src.matchAll(/<x-([a-z0-9-]+(?:\.[a-z0-9-]+)*)/gi)) {
    const name = m[1];
    if (!fs.existsSync(componentPath(name))) problems.push(`${rel(file)}: অজানা কম্পোনেন্ট <x-${name}>`);
  }
  for (const m of src.matchAll(/@(?:include|includeIf|includeWhen|includeUnless|includeFirst)\(\s*'([^']+)'/g)) {
    const candidates = m[1].split(/\s*,\s*/);
    if (!candidates.some((c) => fs.existsSync(viewPath(c.trim())))) {
      problems.push(`${rel(file)}: অজানা ভিউ include('${m[1]}')`);
    }
  }
  for (const m of src.matchAll(/view\(\s*'([^']+)'/g)) {
    if (!fs.existsSync(viewPath(m[1]))) problems.push(`${rel(file)}: অজানা ভিউ view('${m[1]}')`);
  }
}
notes.push('Blade কম্পোনেন্ট/include/view পথ যাচাই');

/* ---------------------------------------------------------------- 4. route() কল বনাম routes/web.php */
const routesSrc = read(path.join(ROOT, 'routes/web.php'));

/**
 * routes/web.php এ সংজ্ঞায়িত সব রাউট নাম।
 * গ্রুপ প্রিফিক্স (Route::prefix('admin')->name('admin.')) যুক্ত করে পূর্ণ নাম তৈরি করা হয়,
 * পাশাপাশি খালি নামগুলোও রাখা হয় — যাতে গ্রুপ-প্রিফিক্স ছাড়াও মিল যাচাই করা যায়।
 */
const definedRouteNames = new Set([...routesSrc.matchAll(/->name\('([^']*)'\)/g)].map((m) => m[1]).filter((n) => n !== ''));
const routeNames = new Set([
  ...definedRouteNames,
  ...[...definedRouteNames].map((n) => 'admin.' + n),
]);

function routeExists(name) {
  if (routeNames.has(name)) return true;
  // গ্রুপ প্রিফিক্স যুক্ত হলে শেষাংশ মিলিয়ে দেখা হয় (যেমন 'admin.news.index' ↔ 'news.index')
  return [...definedRouteNames].some((n) => name.endsWith('.' + n));
}

const allPhpAndBlade = [...bladeFiles, ...phpFiles];
for (const file of allPhpAndBlade) {
  const src = read(file);
  // request()->route('slug') ধরনের কল বাদ দিতে '->' এর পরের route() ধরা হয় না
  for (const m of src.matchAll(/(^|[^>\w$])route\(\s*'([a-zA-Z0-9_.\-]+)'/g)) {
    const name = m[2];
    // রাউট-প্যারামিটার নেম (route('slug')) — এগুলো রাউট নয়
    if (!name.includes('.')) continue;
    if (!routeExists(name)) problems.push(`${rel(file)}: অজানা রাউট route('${name}')`);
  }
}
notes.push(`রাউট নাম যাচাই (${routeNames.size}টি রাউট)`);

/* ---------------------------------------------------------------- 5. site_setting() কী বনাম সেটিংস সংজ্ঞা */
const settingCtrl = read(path.join(ROOT, 'app/Http/Controllers/Admin/SettingController.php'));
const seoCtrl = read(path.join(ROOT, 'app/Http/Controllers/Admin/SeoController.php'));
const seeder = read(path.join(ROOT, 'database/seeders/SettingSeeder.php'));
const defined = new Set([
  ...[...settingCtrl.matchAll(/'key'\s*=>\s*'([^']+)'/g)].map((m) => m[1]),
  ...[...settingCtrl.matchAll(/\$rules\['([^']+)_url'\]/g)].map((m) => m[1] + '_url'),
  ...[...seoCtrl.matchAll(/'key'\s*=>\s*'([^']+)'/g)].map((m) => m[1]),
  'seo_og_image', 'seo_og_image_url',
  ...[...seeder.matchAll(/'([a-z0-9_]+)'\s*=>/g)].map((m) => m[1]),
  ...[...seeder.matchAll(/Setting::put\('([^']+)'/g)].map((m) => m[1]),
  'social_links',
]);
const usedKeys = new Map();
for (const file of [...bladeFiles, ...phpFiles]) {
  const src = read(file);
  for (const m of src.matchAll(/(?:site_setting|mc_flag|Setting::get)\(\s*'([a-z0-9_]+)'/g)) {
    if (!usedKeys.has(m[1])) usedKeys.set(m[1], rel(file));
  }
}
for (const [key, where] of usedKeys) {
  if (!defined.has(key)) problems.push(`${where}: সেটিংস কী '${key}' কোথাও সংজ্ঞায়িত/সিড করা নেই (অ্যাডমিন প্যানেলেও নেই)`);
}
notes.push(`সেটিংস কী যাচাই (${usedKeys.size}টি ব্যবহৃত, ${defined.size}টি সংজ্ঞায়িত)`);

/* ---------------------------------------------------------------- 6. সেটিংস ফিল্ড ↔ ভিউ গ্রুপ ট্যাব */
const settingsView = read(path.join(ROOT, 'resources/views/admin/settings/edit.blade.php'));
for (const g of settingCtrl.matchAll(/^        '([a-z]+)' => \[$/gm)) {
  if (!settingsView.includes(`'${g[1]}'        =>`) && !settingsView.includes(`'${g[1]}'  `) && !settingsView.includes(`'${g[1]}'`)) {
    problems.push(`admin/settings/edit.blade.php: '${g[1]}' গ্রুপের ট্যাব টাইটেল/আইকন নেই`);
  }
}

/* ---------------------------------------------------------------- 7. কম্পাইল করা CSS-এ ব্যবহৃত utility ক্লাস আছে কি না
   (public/css/app.css কমিট করা থাকে এবং প্রোডাকশনে বিল্ড হয় না —
    তাই ভিউতে নতুন ক্লাস যোগ করলে CSS রিবিল্ড করা বাধ্যতামূলক। এটি সেটিই ধরে।) */
const css = read(path.join(ROOT, 'public/css/app.css'));
const missingCss = new Set();
const esc = (c) => c.replace(/[.:\\/\[\]#%(),]/g, (ch) => '\\' + ch);

for (const file of bladeFiles) {
  const src = read(file);
  for (const m of src.matchAll(/class="([^"{]*)"/g)) {
    for (const cls of m[1].split(/\s+/)) {
      if (!cls || cls.startsWith('@') || !/^[a-zA-Z0-9:\[\]#%.,()/_-]+$/.test(cls)) continue;
      if (!css.includes('.' + esc(cls))) missingCss.add(cls);
    }
  }
}
// যেগুলো অন্য শীটে (admin.css/site.css) বা আইকন ফন্টে আছে সেগুলো বাদ
const otherCss = read(path.join(ROOT, 'public/css/admin.css')) + read(path.join(ROOT, 'public/css/site.css'));
const reported = [...missingCss].filter((c) => !otherCss.includes('.' + c) && !c.startsWith('ph'));
notes.push(`CSS ক্লাস কভারেজ: ${reported.length}টি ক্লাস কোনো স্টাইলশীটে পাওয়া যায়নি`);
if (reported.length) notes.push('উদাহরণ: ' + reported.slice(0, 15).join(' | '));

/* ---------------------------------------------------------------- ফলাফল */
console.log('\n===== যাচাই নোট =====');
notes.forEach((n) => console.log(' • ' + n));
console.log('\n===== সমস্যা =====');
if (!problems.length) console.log(' কোনো সমস্যা পাওয়া যায়নি ✔');
else problems.slice(0, 80).forEach((p) => console.log(' ✗ ' + p));
console.log(`\nমোট সমস্যা: ${problems.length}`);
process.exit(problems.length ? 1 : 0);
