/* ==========================================================================
   দৈনিক মহালছড়ি নিউজ — সাইট স্ক্রিপ্ট (কোনো Node.js নির্ভরতা নেই)
   ডার্ক মোড, লাইভ ঘড়ি, সার্চ ওভারলে, মেনু ড্রয়ার, লাইটবক্স, কপি-লিংক
   ========================================================================== */
(function () {
    'use strict';

    var root = document.documentElement;

    /* ===== ডার্ক মোড (ডেমোর লজিক অক্ষুণ্ণ) ===== */
    function applyTheme(theme) {
        if (theme === 'dark') { root.classList.add('dark'); } else { root.classList.remove('dark'); }
        try { localStorage.setItem('mc_theme', theme); document.cookie = 'mc_theme=' + theme + ';path=/;max-age=31536000;samesite=lax'; } catch (e) {}
    }

    (function initTheme() {
        var saved = null;
        try { saved = localStorage.getItem('mc_theme'); } catch (e) {}
        if (!saved) { saved = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
        applyTheme(saved);
    })();

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-mc-theme-toggle]');
        if (t) { applyTheme(root.classList.contains('dark') ? 'light' : 'dark'); }
    });

    /* ===== লাইভ ঘড়ি ও বাংলা তারিখ (bn-BD) ===== */
    var bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    function toBn(v) { return String(v).replace(/[0-9]/g, function (d) { return bnDigits[+d]; }); }

    function tickClock() {
        var now = new Date();
        var time = '';
        try {
            time = new Intl.DateTimeFormat('bn-BD', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }).format(now);
        } catch (e) {
            var h = now.getHours() % 12 || 12, m = now.getMinutes(), s = now.getSeconds();
            time = toBn(h) + ':' + toBn(String(m).padStart(2, '0')) + ':' + toBn(String(s).padStart(2, '0'));
        }
        document.querySelectorAll('[data-mc-clock]').forEach(function (el) { el.textContent = time; });
        document.querySelectorAll('[data-mc-clock-live]').forEach(function (el) { el.textContent = 'সময়: ' + time; });

        var days = ['রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার','শনিবার'];
        var months = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        var dateStr = days[now.getDay()] + ', ' + toBn(now.getDate()) + ' ' + months[now.getMonth()] + ' ' + toBn(now.getFullYear());
        document.querySelectorAll('[data-mc-date]').forEach(function (el) { el.textContent = dateStr; });
    }
    tickClock();
    setInterval(tickClock, 1000);

    /* ===== সার্চ ওভারলে + লাইভ সাজেশন ===== */
    var overlay = document.getElementById('mc-search-overlay');
    var searchInput = document.getElementById('mc-search-input');
    var resultsBox = document.getElementById('mc-search-results');
    var suggestUrl = (window.location.origin || '') + '/search/suggest';
    var debounceTimer = null;

    function openSearch() {
        if (!overlay) return;
        overlay.classList.remove('hidden');
        root.classList.add('mc-lock');
        if (searchInput) searchInput.focus();
    }
    function closeSearch() {
        if (!overlay) return;
        overlay.classList.add('hidden');
        root.classList.remove('mc-lock');
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-mc-search-open]')) { openSearch(); }
        if (e.target.closest('[data-mc-search-close]')) { closeSearch(); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeSearch(); closeMenu(); closeLightbox(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openSearch(); }
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var q = searchInput.value.trim();
            if (q.length < 2) { if (resultsBox) resultsBox.innerHTML = ''; return; }

            debounceTimer = setTimeout(function () {
                fetch(suggestUrl + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { renderSuggestions(data.results || []); })
                    .catch(function () {});
            }, 250);
        });
    }

    function renderSuggestions(results) {
        if (!resultsBox) return;
        if (!results.length) {
            resultsBox.innerHTML = '<p class="py-4 text-center text-sm text-gray-500">কোনো ফলাফল পাওয়া যায়নি</p>';
            return;
        }
        resultsBox.innerHTML = results.map(function (r) {
            var img = r.image ? '<img src="' + r.image + '" alt="" loading="lazy" class="h-12 w-16 shrink-0 rounded object-cover">' : '';
            return '<a href="' + r.url + '" class="flex items-center gap-3 rounded-lg border border-gray-100 bg-white p-2 hover:border-[#D50E18] dark:border-[#263246] dark:bg-[#0D1422]">'
                + img
                + '<span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold text-gray-800 dark:text-[#F1F5F9]">' + escapeHtml(r.title) + '</span>'
                + '<span class="text-[10px] text-gray-500">' + escapeHtml(r.date || '') + ' • ' + escapeHtml(String(r.views || '')) + ' বার</span></span></a>';
        }).join('');
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* ===== মেনু ড্রয়ার ===== */
    var drawer = document.getElementById('mc-menu-drawer');
    function openMenu() { if (drawer) { drawer.classList.remove('hidden'); root.classList.add('mc-lock'); } }
    function closeMenu() { if (drawer) { drawer.classList.add('hidden'); root.classList.remove('mc-lock'); } }
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-mc-menu-open]')) { openMenu(); }
        if (e.target.closest('[data-mc-menu-close]')) { closeMenu(); }
    });

    /* ===== লাইটবক্স ===== */
    var lightbox = document.getElementById('mc-lightbox');
    var lightboxImg = document.getElementById('mc-lightbox-img');
    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        root.classList.remove('mc-lock');
    }
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-mc-lightbox]');
        if (trigger && lightbox && lightboxImg) {
            lightboxImg.src = trigger.getAttribute('data-mc-lightbox');
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            root.classList.add('mc-lock');
        }
        if (e.target.closest('[data-mc-lightbox-close]')) { closeLightbox(); }
    });

    /* ===== লিংক কপি ===== */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-mc-copy]');
        if (!btn) return;
        var text = btn.getAttribute('data-mc-copy');
        var done = function () {
            var old = btn.innerHTML;
            btn.innerHTML = '<i class="ph-fill ph-check"></i>';
            setTimeout(function () { btn.innerHTML = old; }, 1500);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
        } else { fallbackCopy(text, done); }
    });

    function fallbackCopy(text, cb) {
        var ta = document.createElement('textarea');
        ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); cb(); } catch (e) {}
        document.body.removeChild(ta);
    }

    /* ===== ফ্ল্যাশ মেসেজ অটো-হাইড ===== */
    setTimeout(function () {
        document.querySelectorAll('.mc-toast').forEach(function (el) {
            el.style.transition = 'opacity .4s ease'; el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 450);
        });
    }, 6000);

    /* ===== লেজি ইমেজ (native lazy loading fallback) ===== */
    if (!('loading' in HTMLImageElement.prototype) && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    var img = en.target;
                    if (img.dataset.src) { img.src = img.dataset.src; }
                    io.unobserve(img);
                }
            });
        });
        document.querySelectorAll('img[data-src]').forEach(function (img) { io.observe(img); });
    }
})();
