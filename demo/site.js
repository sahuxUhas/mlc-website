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

    /* ==========================================================================
       NEWS DETAILS — শেয়ার / কপি লিংক / মন্তব্য ফর্ম (compact)
       ========================================================================== */

    function mcCopyText(text, ok, fail) {
        var legacy = function () {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.top = '-1000px';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            ta.setSelectionRange(0, ta.value.length);
            var done = false;
            try { done = document.execCommand('copy'); } catch (e) { done = false; }
            document.body.removeChild(ta);
            if (done) { ok(); } else if (fail) { fail(); }
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(ok, legacy);
        } else {
            legacy();
        }
    }

    function mcShowCopyMsg(text) {
        var el = document.querySelector('[data-mc-copy-msg]');
        if (!el) { return; }
        el.textContent = text;
        el.classList.add('is-on');
        clearTimeout(el._mcTimer);
        el._mcTimer = setTimeout(function () { el.classList.remove('is-on'); }, 3000);
    }

    var MC_COPY_OK = 'নিউজ লিংক কপি হয়েছে ✓';

    // মোবাইল ডিভাইসে native share (navigator.share) থাকলে অতিরিক্ত বাটন দেখাও
    if (navigator.share) { document.body.classList.add('mc-has-share'); }

    document.addEventListener('click', function (e) {
        var i, label;

        /* ---- কপি লিংক (canonical URL) ---- */
        var copyBtn = e.target.closest('[data-mc-share-copy]');
        if (copyBtn) {
            var copyUrl = copyBtn.getAttribute('data-url') || window.location.href;
            label = copyBtn.querySelector('span');
            var oldLabel = label ? label.textContent : '';
            mcCopyText(copyUrl, function () {
                mcShowCopyMsg(MC_COPY_OK);
                if (label) {
                    label.textContent = 'কপি হয়েছে ✓';
                    setTimeout(function () { label.textContent = oldLabel; }, 2000);
                }
            }, function () {
                mcShowCopyMsg('লিংক কপি করা যায়নি — ঠিকানা বার থেকে কপি করুন');
            });
            return;
        }

        /* ---- native share (navigator.share) ---- */
        var nativeBtn = e.target.closest('[data-mc-share-native]');
        if (nativeBtn) {
            if (!navigator.share) { return; }
            navigator.share({
                title: nativeBtn.getAttribute('data-title') || document.title,
                text: nativeBtn.getAttribute('data-title') || '',
                url: nativeBtn.getAttribute('data-url') || window.location.href
            }).catch(function () {});
            return;
        }

        /* ---- Messenger ---- */
        var msgrBtn = e.target.closest('[data-mc-share-messenger]');
        if (msgrBtn) {
            var mUrl = msgrBtn.getAttribute('data-url') || window.location.href;
            var mApp = msgrBtn.getAttribute('data-app-id') || '';
            var enc = encodeURIComponent(mUrl);
            var isMobile = /android|iphone|ipad|ipod|iemobile|opera mini/i.test(navigator.userAgent || '');

            if (mApp) {
                window.open('https://www.facebook.com/dialog/send?app_id=' + encodeURIComponent(mApp) + '&link=' + enc + '&redirect_uri=' + enc, '_blank', 'noopener');
                return;
            }
            if (isMobile) {
                window.location.href = 'fb-messenger://share/?link=' + enc;
                setTimeout(function () {
                    window.open('https://www.facebook.com/dialog/send?link=' + enc + '&redirect_uri=' + enc, '_blank', 'noopener');
                }, 1200);
                return;
            }
            if (navigator.share) {
                navigator.share({ title: msgrBtn.getAttribute('data-title') || document.title, url: mUrl }).catch(function () {});
                return;
            }
            // ডেস্কটপে App ID ছাড়া সরাসরি Messenger শেয়ার সীমিত — লিংক কপি করে দিই
            mcCopyText(mUrl, function () {
                mcShowCopyMsg('লিংক কপি হয়েছে — Messenger-এ পেস্ট করে পাঠান ✓');
            }, function () {
                window.open('https://www.facebook.com/dialog/send?link=' + enc + '&redirect_uri=' + enc, '_blank', 'noopener');
            });
            return;
        }

        /* ---- মন্তব্য ফর্ম খোলা/বন্ধ (compact toggle) ---- */
        var toggle = e.target.closest('[data-mc-comment-toggle]');
        if (toggle) {
            var box = document.getElementById(toggle.getAttribute('aria-controls') || 'mc-comment-form');
            if (!box) { return; }
            var open = !box.classList.contains('is-open');
            box.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            label = toggle.querySelector('span');
            if (label) {
                label.textContent = open
                    ? (toggle.getAttribute('data-close-label') || 'বন্ধ করুন')
                    : (toggle.getAttribute('data-open-label') || '💬 মন্তব্য করুন');
            }
            if (open) {
                i = box.querySelector('input[name="guest_name"]');
                if (i) {
                    setTimeout(function () {
                        try { i.focus({ preventScroll: true }); } catch (err) { i.focus(); }
                    }, 80);
                }
            }
            return;
        }
    });

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
