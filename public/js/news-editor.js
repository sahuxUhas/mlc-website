/**
 * ==========================================================================
 *  সংবাদ তৈরি / সম্পাদনা পেজের ইন্টারঅ্যাকশন
 * ==========================================================================
 *  - ক্যাটাগরি → সাব-ক্যাটাগরি ফিল্টার
 *  - ট্যাগ চিপ (Add / Edit / Delete)
 *  - কনটেন্ট এডিটর টুলবার (HTML ট্যাগ সংযোজন, ছবি আপলোড, লাইভ প্রাকদর্শন)
 *  - গ্যালারি: একাধিক ছবি আপলোড, প্রিভিউ, রিমুভ, রিঅর্ডার, ফিচার্ড সেট
 *  - সেভ না করেই সংবাদের প্রাকদর্শন
 *  - প্রকাশের তারিখ/সময়ের দ্রুত বাটন
 *
 *  নিরাপত্তা: ImgBB API Key বা raw hosting URL কখনো এখানে আসে না —
 *  শুধু নিজের ডোমেইনের signed proxy URL (window.MC_NEWS.mediaMap) ব্যবহৃত হয়।
 */
(function () {
    'use strict';

    var CFG = window.MC_NEWS || {};
    var form = document.getElementById('newsForm');
    if (!form) return;

    /* ============================ হেল্পার ============================ */
    function qs(sel, root) { return (root || document).querySelector(sel); }
    function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
    function val(sel) { var el = qs(sel); return el ? String(el.value || '').trim() : ''; }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    var BN_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    var BN_MONTHS = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
    function bnNum(v) { return String(v == null ? '' : v).replace(/[0-9]/g, function (d) { return BN_DIGITS[+d]; }); }
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function csrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }
    function toast(message, ok) {
        var el = document.createElement('div');
        el.className = 'mc-toast-inline ' + (ok === false ? 'is-error' : 'is-ok');
        el.innerHTML = '<i class="ph-fill ' + (ok === false ? 'ph-warning-circle' : 'ph-check-circle') + '"></i> ' + esc(message);
        document.body.appendChild(el);
        setTimeout(function () {
            el.style.transition = 'opacity .3s ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 350);
        }, 3200);
    }
    function messageFor(data, fallback) {
        if (!data) return fallback;
        if (data.message) return data.message;
        if (data.errors && data.errors.length) return data.errors[0];
        if (typeof data.errors === 'object') {
            var first = Object.keys(data.errors)[0];
            if (first && data.errors[first][0]) return data.errors[first][0];
        }
        return fallback;
    }
    function post(url, formData) {
        formData.append('_token', csrf());
        return fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return { success: res.ok }; }).then(function (data) {
                data.__status = res.status;
                return data;
            });
        });
    }

    /* ======================= ১. সাব-ক্যাটাগরি ফিল্টার ======================= */
    (function subcategories() {
        var catSel = qs('#category_id');
        var subSel = qs('#subcategory_id');
        if (!catSel || !subSel) return;

        var previous = subSel.getAttribute('data-selected') || subSel.value || '';

        function filter(preserve) {
            var cat = catSel.value;
            var kept = false;

            qsa('option', subSel).forEach(function (opt) {
                if (!opt.value) return;
                var match = !cat || opt.getAttribute('data-parent') === cat;
                opt.hidden = !match;
                opt.disabled = !match;
                if (opt.selected && match) kept = true;
            });

            if (!kept) {
                subSel.value = '';
                if (preserve && previous) {
                    qsa('option', subSel).forEach(function (opt) {
                        if (opt.value === previous && !opt.disabled) subSel.value = previous;
                    });
                }
            }
            previous = subSel.value;
        }

        catSel.addEventListener('change', function () { subSel.value = ''; previous = ''; filter(false); });
        filter(true);
    })();

    /* ============================ ২. ট্যাগ চিপ ============================ */
    (function tags() {
        var box = qs('[data-tag-box]');
        var input = qs('#tagInput');
        var hidden = qs('#tagsField');
        if (!box || !input || !hidden) return;

        function current() {
            return hidden.value.split(',').map(function (t) { return t.trim(); }).filter(Boolean);
        }

        function save(list) {
            hidden.value = list.join(', ');
            box.setAttribute('data-tag-count', String(list.length));
        }

        function chip(name) {
            var el = document.createElement('span');
            el.className = 'mc-tag-chip';
            el.setAttribute('data-tag', name);
            el.innerHTML = '<span>' + esc(name) + '</span><button type="button" title="ট্যাগ মুছুন" aria-label="মুছুন">✕</button>';
            return el;
        }

        function render() {
            qsa('[data-tag]', box).forEach(function (el) { el.remove(); });
            current().forEach(function (name) { box.insertBefore(chip(name), input); });
        }

        function add(name) {
            name = String(name || '').trim().replace(/\s+/g, ' ');
            if (!name) return;
            var list = current();
            if (list.some(function (t) { return t.toLowerCase() === name.toLowerCase(); })) {
                toast('এই ট্যাগ আগেই যোগ করা আছে');
                return;
            }
            if (name.length > 60) { toast('ট্যাগ খুব বড় (সর্বোচ্চ ৬০ অক্ষর)', false); return; }
            list.push(name);
            save(list);
            render();
        }

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',' || e.key === '،') {
                e.preventDefault();
                add(input.value);
                input.value = '';
            } else if (e.key === 'Backspace' && input.value === '') {
                var list = current();
                list.pop();
                save(list);
                render();
            }
        });

        input.addEventListener('blur', function () {
            if (input.value.trim() !== '') { add(input.value); input.value = ''; }
        });

        box.addEventListener('click', function (e) {
            var btn = e.target.closest('.mc-tag-chip button');
            if (!btn) return;
            var name = btn.closest('[data-tag]').getAttribute('data-tag');
            save(current().filter(function (t) { return t !== name; }));
            render();
        });

        // JS ছাড়া ব্যবহারের সময় কমা-বিচ্ছিন্ন ইনপুটই সঠিকভাবে যায়; এখানে শুধু চিপ দেখানো হয়
        render();
        save(hidden.value.split(',').map(function (t) { return t.trim(); }).filter(Boolean));
    })();

    /* ====================== ৩. কনটেন্ট এডিটর ====================== */
    (function contentEditor() {
        var wrap = qs('[data-editor]');
        var ta = qs('#content');
        if (!wrap || !ta) return null;

        var countEl = qs('#content-count');
        var wordsEl = qs('#content-words');
        var statusEl = qs('#editor-upload-status');
        var mediaList = qs('#editor-media-list');
        var mediaInput = qs('#editorImages');

        function refreshCounters() {
            var text = ta.value.replace(/\{\{media:\d+[^}]*\}\}/g, ' ');
            if (countEl) countEl.textContent = bnNum(text.length);
            if (wordsEl) wordsEl.textContent = bnNum((text.trim().match(/\S+/g) || []).length);
        }

        function insert(text, selectFrom) {
            var start = ta.selectionStart, end = ta.selectionEnd;
            ta.setRangeText(text, start, end, 'end');
            if (selectFrom != null) {
                var pos = start + selectFrom;
                ta.setSelectionRange(pos, pos + 1);
            }
            ta.focus();
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            refreshCounters();
            scheduleEditorPreview();
        }

        function wrapSelection(before, after, placeholder) {
            var start = ta.selectionStart, end = ta.selectionEnd;
            var selected = ta.value.slice(start, end) || placeholder || '';
            var text = before + selected + after;
            ta.setRangeText(text, start, end, 'end');
            ta.focus();
            if (!ta.value.slice(start, end)) ta.setSelectionRange(start + before.length, start + before.length + selected.length);
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            refreshCounters();
            scheduleEditorPreview();
        }

        function block(html) {
            var start = ta.selectionStart;
            var prefix = start > 0 && ta.value[start - 1] !== '\n' ? '\n' : '';
            insert(prefix + html + '\n');
        }

        function toggle(btn, on) { btn.classList.toggle('is-active', !!on); }

        qsa('[data-ed-block]', wrap).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var kind = btn.getAttribute('data-ed-block');
                if (kind === 'ul') return block('<ul>\n  <li>প্রথম পয়েন্ট</li>\n  <li>দ্বিতীয় পয়েন্ট</li>\n</ul>');
                if (kind === 'ol') return block('<ol>\n  <li>প্রথম পয়েন্ট</li>\n  <li>দ্বিতীয় পয়েন্ট</li>\n</ol>');
                if (kind === 'quote') return block('<blockquote>এখানে গুরুত্বপূর্ণ উদ্ধৃতি লিখুন</blockquote>');
                if (kind === 'hr') return block('<hr>');
                wrapSelection('<' + kind + '>', '</' + kind + '>', 'উপ-শিরোনাম');
            });
        });

        qsa('[data-ed-wrap]', wrap).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tag = btn.getAttribute('data-ed-wrap');
                wrapSelection('<' + tag + '>', '</' + tag + '>', 'লেখা');
            });
        });

        var linkBtn = qs('[data-ed-link]', wrap);
        if (linkBtn) {
            linkBtn.addEventListener('click', function () {
                var start = ta.selectionStart, end = ta.selectionEnd;
                var selected = ta.value.slice(start, end);
                var url = window.prompt('লিংকের ঠিকানা (https://…)', 'https://');
                if (!url) return;
                if (!/^https?:\/\//i.test(url)) { toast('লিংকটি https:// দিয়ে শুরু হতে হবে', false); return; }
                insert('<a href="' + esc(url) + '" target="_blank" rel="noopener">' + esc(selected || 'লিংক টেক্সট') + '</a>');
            });
        }

        /* ---- ছবি আপলোড (ImgBB → DB রেফারেন্স → {{media:ID}}) ---- */
        var imageBtn = qs('[data-ed-image]', wrap);
        if (imageBtn && mediaInput) {
            imageBtn.addEventListener('click', function () { mediaInput.click(); });

            mediaInput.addEventListener('change', function () {
                if (!mediaInput.files || !mediaInput.files.length) return;
                var files = Array.prototype.slice.call(mediaInput.files).slice(0, 10);

                var tooBig = files.some(function (f) { return f.size / 1024 > (CFG.maxKb || 4096); });
                if (tooBig) {
                    toast('ছবির সাইজ সর্বোচ্চ ' + bnNum(Math.floor((CFG.maxKb || 4096) / 1024)) + ' MB', false);
                    return;
                }

                var fd = new FormData();
                files.forEach(function (f) { fd.append('images[]', f); });
                if (CFG.postId) fd.append('post_id', CFG.postId);

                if (statusEl) { statusEl.textContent = 'ছবি আপলোড হচ্ছে…'; statusEl.classList.remove('hidden'); }

                post(CFG.routes.editorMedia || '/admin/news/media/upload', fd).then(function (data) {
                    if (statusEl) statusEl.classList.add('hidden');

                    if (data.success && data.images && data.images.length) {
                        var snippet = data.images.map(function (item) {
                            CFG.mediaMap[item.id] = { id: item.id, thumb: item.thumb, name: item.name };
                            addEditorMediaTile(item);
                            return item.key;
                        }).join('\n');
                        insert('\n' + snippet + '\n');
                        toast(data.message || 'ছবি যোগ করা হয়েছে');
                    } else {
                        toast(messageFor(data, 'ছবি যোগ করা যায়নি'), false);
                    }

                    if (data.errors && data.errors.length) toast(data.errors[0], false);
                    mediaInput.value = '';
                }).catch(function () {
                    if (statusEl) statusEl.classList.add('hidden');
                    toast('ছবি আপলোডে সংযোগ সমস্যা — আবার চেষ্টা করুন', false);
                    mediaInput.value = '';
                });
            });
        }

        function addEditorMediaTile(item) {
            if (!mediaList) return;
            mediaList.classList.remove('hidden');
            if (qs('[data-editor-media="' + item.id + '"]', mediaList)) return;

            var tile = document.createElement('div');
            tile.className = 'rounded-lg border border-slate-200 bg-white p-1 dark:border-slate-700 dark:bg-slate-800';
            tile.setAttribute('data-editor-media', item.id);
            tile.innerHTML = '<img src="' + esc(item.thumb) + '" alt="' + esc(item.name) + '" class="h-9 w-12 rounded object-cover">';
            mediaList.appendChild(tile);
        }

        qsa('[data-ed-remove-media]', document).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-ed-remove-media');
                ta.value = ta.value.replace(new RegExp('\\{\\{media:' + id + '(\\|[^}]*)?\\}\\}\\s*', 'g'), '');
                ta.dispatchEvent(new Event('input', { bubbles: true }));
                refreshCounters();
                scheduleEditorPreview();
                var tile = qs('[data-editor-media="' + id + '"]');
                if (tile) tile.remove();
                toast('ছবিটি লেখা থেকে সরানো হয়েছে (মিডিয়া লাইব্রেরিতে আছে)');
            });
        });

        /* ---- ফুলস্ক্রিন ---- */
        var fullBtn = qs('[data-ed-full]', wrap);
        if (fullBtn) {
            fullBtn.addEventListener('click', function () {
                wrap.classList.toggle('is-fullscreen');
                toggle(fullBtn, wrap.classList.contains('is-fullscreen'));
                document.documentElement.classList.toggle('mc-lock', wrap.classList.contains('is-fullscreen'));
                ta.focus();
            });
        }

        /* ---- লাইভ প্রাকদর্শন ---- */
        var previewBtn = qs('[data-ed-preview]', wrap);
        var previewPanel = qs('#editor-preview-panel');
        var previewFrame = qs('#editor-preview-frame');
        var previewOn = false;
        var previewTimer = null;

        function renderEditorPreview() {
            if (!previewOn || !previewFrame) return;
            previewFrame.srcdoc = buildPreviewDocument({ compact: true });
        }

        function scheduleEditorPreview() {
            if (!previewOn || !previewFrame) return;
            clearTimeout(previewTimer);
            previewTimer = setTimeout(renderEditorPreview, 500);
        }

        if (previewBtn && previewPanel) {
            previewBtn.addEventListener('click', function () {
                previewOn = !previewOn;
                previewPanel.classList.toggle('hidden', !previewOn);
                toggle(previewBtn, previewOn);
                renderEditorPreview();
            });
        }

        ta.addEventListener('input', refreshCounters);
        refreshCounters();
    })();

    /* ==================== ৪. প্রাকদর্শন ডকুমেন্ট তৈরি ==================== */
    function replaceMediaReferences(html) {
        return String(html || '').replace(/\{\{\s*media\s*:\s*(\d+)\s*(?:\|\s*([^}]*))?\}\}/g, function (m, id, caption) {
            var item = CFG.mediaMap ? CFG.mediaMap[id] : null;
            if (!item) return '';
            return '<figure style="margin:1.25rem 0"><img src="' + esc(item.thumb) + '" alt="' + esc(caption || item.name || '') +
                '" style="width:100%;border-radius:.75rem"><figcaption style="font-size:.8rem;color:#64748b;margin-top:.35rem">' + esc(caption || '') + '</figcaption></figure>';
        });
    }

    function featuredPreviewSource() {
        var input = qs('#featured_image');
        var img = qs('#featured_image-preview');
        if (input && input.files && input.files[0] && window.__mcFeaturedDataUrl) return window.__mcFeaturedDataUrl;
        if (img && img.getAttribute('src')) return img.getAttribute('src');
        return '';
    }

    /* নির্বাচিত ফাইলের ছবি (data URL) — প্রাকদর্শনে দেখানোর জন্য */
    (function featuredDataUrl() {
        var input = qs('#featured_image');
        if (!input) return;
        input.addEventListener('change', function () {
            window.__mcFeaturedDataUrl = '';
            if (!input.files || !input.files[0]) return;
            var reader = new FileReader();
            reader.onload = function (e) { window.__mcFeaturedDataUrl = e.target.result; };
            reader.readAsDataURL(input.files[0]);
        });
    })();

    function buildPreviewDocument(options) {
        var compact = options && options.compact;
        var title = val('#title') || 'সংবাদের শিরোনাম এখানে দেখাবে';
        var excerpt = val('#excerpt');
        var content = replaceMediaReferences(val('#content'));
        var cat = qs('#category_id') ? (qs('#category_id').selectedOptions[0] || {}).text : '';
        var reporter = qs('#reporter_id') ? (qs('#reporter_id').selectedOptions[0] || {}).text : '';
        var dateText = humanDate(val('#published_date'), val('#published_time'));
        var image = featuredPreviewSource();
        var tags = (qs('#tagsField') ? qs('#tagsField').value : '').split(',').map(function (t) { return t.trim(); }).filter(Boolean);

        var galleryHtml = '';
        if (!compact) {
            var tiles = qsa('[data-gallery-item] img').concat(qsa('#images-preview img'));
            if (tiles.length) {
                galleryHtml = '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:1.5rem">' +
                    tiles.slice(0, 12).map(function (img) {
                        return '<img src="' + esc(img.getAttribute('src')) + '" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:.6rem">';
                    }).join('') + '</div>';
            }
        }

        var body = '' +
            '<header style="border-bottom:4px solid #D50E18;padding-bottom:.5rem;margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center">' +
                '<strong style="font-size:1.1rem;color:#0B0B0B">দৈনিক মহালছড়ি নিউজ</strong>' +
                '<span style="font-size:.7rem;color:#E21D2B;font-weight:700">প্রাকদর্শন</span>' +
            '</header>' +
            (cat ? '<span style="background:#E21D2B;color:#fff;font-size:.7rem;font-weight:700;padding:.15rem .5rem;border-radius:.25rem">' + esc(cat) + '</span>' : '') +
            '<h1 style="font-family:\'Tiro Bangla\',serif;font-size:' + (compact ? '1.25rem' : '1.75rem') + ';line-height:1.4;margin:.6rem 0">' + esc(title) + '</h1>' +
            '<p style="font-size:.75rem;color:#64748b;margin:0 0 1rem">' +
                (reporter ? esc(reporter) + ' · ' : '') + esc(dateText) +
            '</p>' +
            (image ? '<img src="' + esc(image) + '" alt="" style="width:100%;border-radius:.75rem;margin-bottom:1rem" onerror="this.style.display=\'none\'">' : '') +
            (excerpt ? '<p style="font-size:1rem;font-weight:600;color:#334155;border-left:3px solid #E21D2B;padding-left:.75rem;margin:0 0 1rem">' + esc(excerpt) + '</p>' : '') +
            '<div style="font-size:' + (compact ? '.9rem' : '1.02rem') + ';line-height:2;color:#1f2937">' + (content || '<em style="color:#94a3b8">সংবাদের বিস্তারিত এখানে দেখা যাবে…</em>') + '</div>' +
            galleryHtml +
            (tags.length ? '<p style="margin-top:1.25rem;font-size:.75rem;color:#64748b">ট্যাগ: ' + tags.map(esc).join(', ') + '</p>' : '');

        return '<!doctype html><html lang="bn"><head><meta charset="utf-8">' +
            '<meta name="viewport" content="width=device-width,initial-scale=1">' +
            '<style>body{margin:0;padding:' + (compact ? '1rem' : '1.5rem') + ';background:#fff;color:#0f172a;font-family:\'Hind Siliguri\',\'Noto Sans Bengali\',system-ui,sans-serif}' +
            'img{max-width:100%}ul,ol{padding-left:1.25rem}blockquote{border-left:3px solid #E21D2B;margin:1rem 0;padding-left:.75rem;color:#475569}</style>' +
            '</head><body>' + body + '</body></html>';
    }

    /* ---- প্রাকদর্শন মোডাল ---- */
    (function previewModal() {
        var frame = qs('#newsPreviewFrame');
        if (!frame) return;

        function refresh() { frame.srcdoc = buildPreviewDocument({ compact: false }); }

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-mc-modal-open="newsPreviewModal"]')) setTimeout(refresh, 30);

            var device = e.target.closest('[data-preview-device]');
            if (device) frame.classList.toggle('is-mobile', device.getAttribute('data-preview-device') === 'mobile');

            if (e.target.closest('[data-preview-refresh]')) refresh();
        });
    })();

    /* ==================== ৫. তারিখ ও সময়ের সহায়ক ==================== */
    function humanDate(dateStr, timeStr) {
        if (!dateStr) return 'তারিখ দেওয়া হয়নি';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        var text = bnNum(parseInt(parts[2], 10)) + ' ' + (BN_MONTHS[parseInt(parts[1], 10) - 1] || '') + ' ' + bnNum(parts[0]);

        if (timeStr) {
            var hm = timeStr.split(':');
            var h = parseInt(hm[0], 10), m = parseInt(hm[1], 10) || 0;
            var period = h < 4 ? 'রাত' : h < 6 ? 'ভোর' : h < 12 ? 'সকাল' : h < 16 ? 'দুপুর' : h < 18 ? 'বিকাল' : h < 20 ? 'সন্ধ্যা' : 'রাত';
            var h12 = h % 12 || 12;
            text += ', ' + period + ' ' + bnNum(h12) + ':' + bnNum(pad(m));
        }
        return text;
    }

    (function dateHelpers() {
        function setValue(sel, value) {
            var el = qs(sel);
            if (el) { el.value = value; el.dispatchEvent(new Event('change', { bubbles: true })); }
        }

        function targets(btn) {
            return (btn.getAttribute('data-set-target') || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        }

        function applyTo(selector, date, time) {
            if (selector.indexOf('date') > -1) setValue(selector, date);
            else setValue(selector, time);
        }

        function nowParts() {
            var d = new Date();
            return { date: d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()), time: pad(d.getHours()) + ':' + pad(d.getMinutes()), raw: d };
        }

        document.addEventListener('click', function (e) {
            var nowBtn = e.target.closest('[data-set-now]');
            if (nowBtn) {
                var n = nowParts();
                (nowBtn.getAttribute('data-set-now') || '').split(',').forEach(function (sel) { applyTo(sel.trim(), n.date, n.time); });
                return;
            }

            var timeBtn = e.target.closest('[data-set-time]');
            if (timeBtn) {
                var t = timeBtn.getAttribute('data-set-time');
                targets(timeBtn).forEach(function (sel) { applyTo(sel, qs(sel) && qs(sel).value ? qs(sel).value : nowParts().date, t); });
                return;
            }

            var shiftBtn = e.target.closest('[data-shift]');
            if (shiftBtn) {
                var expr = shiftBtn.getAttribute('data-shift');
                var nums = (expr.match(/\+?(\d+)\s*(hour|day|week)/i) || []);
                if (!nums.length) return;
                var amount = parseInt(nums[1], 10);
                var unit = nums[2].toLowerCase();
                var base = new Date(Date.now() + amount * (unit === 'hour' ? 3600e3 : unit === 'day' ? 86400e3 : 604800e3));
                var date = base.getFullYear() + '-' + pad(base.getMonth() + 1) + '-' + pad(base.getDate());
                var time = pad(base.getHours()) + ':' + pad(base.getMinutes());

                targets(shiftBtn).forEach(function (sel) { applyTo(sel, date, time); });
            }
        });

        function refreshHuman() {
            var pub = qs('#published-human');
            if (pub) pub.textContent = val('#published_date') ? '→ ' + humanDate(val('#published_date'), val('#published_time')) : '';

            var sch = qs('#scheduled-human');
            if (sch) sch.textContent = val('#scheduled_date') ? '→ ' + humanDate(val('#scheduled_date'), val('#scheduled_time')) : '';
        }

        ['#published_date', '#published_time', '#scheduled_date', '#scheduled_time'].forEach(function (sel) {
            var el = qs(sel);
            if (el) el.addEventListener('change', refreshHuman);
        });
        refreshHuman();

        // স্ট্যাটাস অনুযায়ী সংশ্লিষ্ট সময়ের ঘর হাইলাইট
        var statusSel = qs('#status');
        if (statusSel) {
            var sync = function () {
                var scheduled = statusSel.value === 'scheduled';
                var box = qs('#scheduled_date') ? qs('#scheduled_date').closest('div.rounded-lg') : null;
                if (box) box.style.boxShadow = scheduled ? '0 0 0 3px rgba(99,102,241,.25)' : 'none';

                if (scheduled && !val('#scheduled_date')) {
                    var n = nowParts();
                    var later = new Date(Date.now() + 3600e3);
                    setValue('#scheduled_date', later.getFullYear() + '-' + pad(later.getMonth() + 1) + '-' + pad(later.getDate()));
                    setValue('#scheduled_time', pad(later.getHours()) + ':' + pad(later.getMinutes()));
                    refreshHuman();
                }
            };
            statusSel.addEventListener('change', sync);
            sync();
        }
    })();

    /* ==================== ৬. গ্যালারি ম্যানেজার ==================== */
    (function gallery() {
        var list = qs('[data-gallery-list]');
        var input = qs('#images');
        var preview = qs('#images-preview');
        var orderInput = qs('[data-order-input]');
        if (!input) return;

        function galleryItems() { return list ? qsa('[data-gallery-item]', list) : []; }

        /* সহায়ক ফর্মগুলো (nested form এড়াতে মূল ফর্মের বাইরে থাকে) */
        function auxContainer() {
            var box = qs('#newsAuxForms');
            if (!box) {
                box = document.createElement('div');
                box.id = 'newsAuxForms';
                box.className = 'hidden';
                box.setAttribute('aria-hidden', 'true');
                document.body.appendChild(box);
            }
            return box;
        }

        function auxForm(prefix, id) { return document.getElementById(prefix + '-' + id); }

        function removeAuxForms(id) {
            ['cap-', 'feat-', 'del-'].forEach(function (prefix) {
                var el = auxForm(prefix, id);
                if (el) el.remove();
            });
        }

        function renumber() {
            galleryItems().forEach(function (item, index) {
                var badge = qs('[data-order-badge]', item);
                if (badge) badge.textContent = bnNum(index + 1);
            });
        }

        function syncOrder() {
            if (!orderInput) return;
            orderInput.value = galleryItems().map(function (item) { return item.getAttribute('data-id'); }).join(',');
        }

        function persistOrder() {
            syncOrder();
            if (!CFG.isEdit || !CFG.routes.reorder) return;

            var fd = new FormData();
            galleryItems().forEach(function (item) { fd.append('order[]', item.getAttribute('data-id')); });

            post(CFG.routes.reorder, fd)
                .then(function (data) { toast(data.message || 'ছবির ক্রম সংরক্ষিত হয়েছে', data.success !== false); })
                .catch(function () { toast('ক্রম সংরক্ষণ করা যায়নি — আবার চেষ্টা করুন', false); });
        }

        /* ---- ইভেন্ট ডেলিগেশন ---- */
        if (list) {
            list.addEventListener('click', function (e) {
                var item = e.target.closest('[data-gallery-item]');
                if (!item) return;

                var moveBtn = e.target.closest('[data-move]');
                if (moveBtn) {
                    var sibling = moveBtn.getAttribute('data-move') === 'up' ? item.previousElementSibling : item.nextElementSibling;
                    if (sibling && sibling.hasAttribute('data-gallery-item')) {
                        if (moveBtn.getAttribute('data-move') === 'up') list.insertBefore(item, sibling);
                        else list.insertBefore(sibling, item);
                        renumber();
                        persistOrder();
                    }
                    return;
                }

                var captionBtn = e.target.closest('[data-toggle-caption]');
                if (captionBtn) {
                    var panel = qs('[data-caption-panel]', item);
                    if (panel) panel.classList.toggle('hidden');
                    return;
                }

                var featuredBtn = e.target.closest('[data-set-featured]');
                if (featuredBtn) {
                    var featForm = auxForm('feat', item.getAttribute('data-id'));
                    if (!featForm) return;
                    e.preventDefault();
                    post(featForm.getAttribute('action'), new FormData(featForm)).then(function (data) {
                        if (data.success === false) { toast(messageFor(data, 'সেট করা যায়নি'), false); return; }
                        markFeatured(item);
                        toast(data.message || 'এই ছবিটি ফিচার্ড করা হয়েছে');
                    }).catch(function () { featForm.submit(); });
                    return;
                }

                var deleteBtn = e.target.closest('[data-delete-image]');
                if (deleteBtn) {
                    var delForm = auxForm('del', item.getAttribute('data-id'));
                    if (!delForm) return;
                    e.preventDefault();
                    if (!window.confirm(CFG.deleteImageLabel || 'ছবিটি মুছে ফেলবেন?')) return;

                    post(delForm.getAttribute('action'), new FormData(delForm)).then(function (data) {
                        if (data.success === false) { toast(messageFor(data, 'মুছে ফেলা যায়নি'), false); return; }
                        removeAuxForms(item.getAttribute('data-id'));
                        item.remove();
                        renumber();
                        syncOrder();
                        toast(data.message || 'ছবি মুছে ফেলা হয়েছে');
                    }).catch(function () { delForm.submit(); });
                    return;
                }
            });

            /* ক্যাপশন ফর্ম AJAX — ইনপুটগুলো মূল ফর্মের বাইরের cap-* ফর্মের সাথে যুক্ত */
            var auxBox = qs('#newsAuxForms');
            if (auxBox) {
                auxBox.addEventListener('submit', function (e) {
                    var captionForm = e.target.closest('[data-caption-ajax]');
                    if (!captionForm) return;
                    e.preventDefault();

                    var panel = qs('[data-caption-form="' + captionForm.id.replace('cap-', '') + '"]');
                    if (panel) panel.classList.add('hidden');

                    post(captionForm.getAttribute('action'), new FormData(captionForm)).then(function (data) {
                        toast(data.message || 'ছবির তথ্য সংরক্ষিত হয়েছে', data.success !== false);
                    }).catch(function () { captionForm.submit(); });
                });
            }

            /* ---- ড্র্যাগ ও ড্রপ রিঅর্ডার ---- */
            var dragged = null;

            list.addEventListener('dragstart', function (e) {
                var item = e.target.closest('[data-gallery-item]');
                if (!item) return;
                dragged = item;
                item.classList.add('is-dragging');
            });

            list.addEventListener('dragend', function () {
                if (dragged) dragged.classList.remove('is-dragging');
                dragged = null;
            });

            list.addEventListener('dragover', function (e) {
                if (!dragged) return;
                e.preventDefault();
                var target = e.target.closest('[data-gallery-item]');
                if (!target || target === dragged) return;

                var rect = target.getBoundingClientRect();
                var after = (e.clientY - rect.top) > rect.height / 2;
                list.insertBefore(dragged, after ? target.nextElementSibling : target);
            });

            list.addEventListener('drop', function (e) {
                if (!dragged) return;
                e.preventDefault();
                renumber();
                persistOrder();
            });
        }

        function markFeatured(item) {
            galleryItems().forEach(function (el) {
                el.classList.remove('is-featured');
                var badge = qs('.mc-badge-featured', el);
                if (badge) badge.remove();
            });
            item.classList.add('is-featured');
            if (!qs('.mc-badge-featured', item)) {
                var span = document.createElement('span');
                span.className = 'mc-badge-featured';
                span.textContent = 'ফিচার্ড';
                item.insertBefore(span, item.firstChild);
            }
        }

        /* ---- নতুন ছবি: ক্রিয়েট মোডে স্টেজ, এডিট মোডে সাথে সাথে আপলোড ---- */
        function fileList(files) {
            var dt = new DataTransfer();
            files.forEach(function (f) { dt.items.add(f); });
            return dt.files;
        }

        function pendingPreview() {
            if (!preview) return;
            preview.innerHTML = '';
            Array.prototype.forEach.call(input.files, function (file, index) {
                if (!/^image\//.test(file.type)) return;
                var tile = document.createElement('div');
                tile.className = 'mc-upload-tile';
                tile.setAttribute('data-pending-index', index);

                var reader = new FileReader();
                reader.onload = function (e) {
                    tile.innerHTML =
                        '<img src="' + e.target.result + '" alt="">' +
                        '<div class="mc-tile-bar"><span class="truncate">' + esc(file.name) + '</span>' +
                        '<span class="flex gap-1">' +
                        '<button type="button" data-pending-move="up" title="উপরে">▲</button>' +
                        '<button type="button" data-pending-move="down" title="নিচে">▼</button>' +
                        '<button type="button" data-pending-remove title="সরান" class="text-red-600">✕</button>' +
                        '</span></div>';
                };
                reader.readAsDataURL(file);
                preview.appendChild(tile);
            });
        }

        if (preview) {
            preview.addEventListener('click', function (e) {
                var files = Array.prototype.slice.call(input.files);
                var tile = e.target.closest('[data-pending-index]');
                if (!tile) return;
                var index = parseInt(tile.getAttribute('data-pending-index'), 10);

                if (e.target.closest('[data-pending-remove]')) {
                    files.splice(index, 1);
                } else if (e.target.closest('[data-pending-move="up"]') && index > 0) {
                    files.splice(index - 1, 0, files.splice(index, 1)[0]);
                } else if (e.target.closest('[data-pending-move="down"]') && index < files.length - 1) {
                    files.splice(index + 1, 0, files.splice(index, 1)[0]);
                } else {
                    return;
                }

                input.files = fileList(files);
                pendingPreview();
            });
        }

        function validateFiles(files) {
            var max = CFG.maxUploads || 20;
            if (files.length > max) { toast('একসাথে সর্বোচ্চ ' + bnNum(max) + 'টি ছবি আপলোড করা যায়', false); return false; }

            var bad = files.filter(function (f) { return !/^image\/(jpeg|png|webp)$/.test(f.type); });
            if (bad.length) { toast('শুধু jpg, png, webp ছবি গ্রহণ করা হয়', false); return false; }

            var big = files.filter(function (f) { return f.size / 1024 > (CFG.maxKb || 4096); });
            if (big.length) { toast('প্রতিটি ছবি সর্বোচ্চ ' + bnNum(Math.floor((CFG.maxKb || 4096) / 1024)) + ' MB হতে পারবে', false); return false; }

            return true;
        }

        input.addEventListener('change', function () {
            var files = Array.prototype.slice.call(input.files);
            if (!files.length) return;
            if (!validateFiles(files)) { input.value = ''; return; }

            // এডিট মোডে সাথে সাথে হোস্টিং API তে আপলোড (ফর্ম সাবমিটের অপেক্ষা নয়)
            if (CFG.isEdit && CFG.routes.imagesStore) {
                pendingPreview();
                var fd = new FormData();
                files.forEach(function (f) { fd.append('images[]', f); });

                toast('ছবি আপলোড হচ্ছে…');
                post(CFG.routes.imagesStore, fd).then(function (data) {
                    input.value = '';
                    if (preview) preview.innerHTML = '';

                    if (data.images && data.images.length) {
                        data.images.forEach(function (item) {
                            if (list) list.appendChild(buildGalleryItem(item));
                        });
                        renumber();
                        syncOrder();
                        toast(data.message || 'ছবি যোগ হয়েছে');
                    }

                    if (data.errors && data.errors.length) data.errors.forEach(function (msg) { toast(msg, false); });

                    if (!data.success) {
                        toast(messageFor(data, 'ছবি আপলোড করা যায়নি'), false);
                        // ব্যর্থ হলে সাধারণ ফর্ম সাবমিটের জন্য ফাইল রেখে দেওয়া হয়
                        input.files = fileList(files);
                        pendingPreview();
                    }
                }).catch(function () {
                    // নেটওয়ার্ক সমস্যা হলে সাধারণ সাবমিটের মাধ্যমে চেষ্টা হবে
                    pendingPreview();
                    toast('সাথে সাথে আপলোড করা যায়নি — ফর্ম সংরক্ষণ করলে ছবি যুক্ত হবে', false);
                });

                return;
            }

            pendingPreview();
            toast(bnNum(files.length) + 'টি ছবি সংরক্ষণের জন্য প্রস্তুত');
        });

        /* JS দিয়ে তৈরি গ্যালারি আইটেম (API response → DOM) — কোনো nested form নয় */
        function buildGalleryItem(d) {
            var item = document.createElement('div');
            item.className = 'mc-gallery-item';
            item.setAttribute('data-gallery-item', '');
            item.setAttribute('data-id', d.id);
            item.setAttribute('data-media-id', d.media_id || '');
            item.setAttribute('draggable', 'true');

            item.innerHTML =
                '<span class="mc-badge-order" data-order-badge>১</span>' +
                '<img src="' + esc(d.thumb) + '" alt="' + esc(d.label) + '" loading="lazy">' +
                '<div class="mc-gallery-bar">' +
                    '<div class="flex gap-1">' +
                        '<button type="button" data-move="up" title="উপরে"><i class="ph-bold ph-arrow-up"></i></button>' +
                        '<button type="button" data-move="down" title="নিচে"><i class="ph-bold ph-arrow-down"></i></button>' +
                    '</div>' +
                    '<div class="flex gap-1">' +
                        '<button type="button" data-toggle-caption title="ক্যাপশন/ক্রেডিট"><i class="ph-bold ph-text-aa"></i></button>' +
                        '<button type="submit" form="feat-' + d.id + '" data-set-featured title="ফিচার্ড করুন"><i class="ph-bold ph-star"></i></button>' +
                        '<button type="submit" form="del-' + d.id + '" class="is-danger" data-delete-image title="মুছে ফেলুন"><i class="ph-bold ph-trash"></i></button>' +
                    '</div>' +
                '</div>' +
                '<div class="hidden border-t border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-800/60"' +
                    ' data-caption-panel data-caption-form="' + d.id + '">' +
                    '<div class="space-y-1.5">' +
                        '<input type="text" form="cap-' + d.id + '" name="caption" maxlength="190" value="' + esc(d.caption || '') + '" placeholder="ক্যাপশন" class="mc-input text-[11px]">' +
                        '<input type="text" form="cap-' + d.id + '" name="credit" maxlength="120" value="' + esc(d.credit || '') + '" placeholder="ক্রেডিট" class="mc-input text-[11px]">' +
                        '<input type="text" form="cap-' + d.id + '" name="alt_text" maxlength="190" value="' + esc(d.alt_text || '') + '" placeholder="Alt টেক্সট (SEO)" class="mc-input text-[11px]">' +
                        '<button type="submit" form="cap-' + d.id + '" class="mc-btn mc-btn-ghost w-full text-[11px]">সংরক্ষণ</button>' +
                    '</div>' +
                '</div>';

            buildAuxForms(d);

            return item;
        }

        /* সহায়ক ফর্ম তৈরি (মূল ফর্মের বাইরে): ক্যাপশন / ফিচার্ড / ডিলিট */
        function buildAuxForms(d) {
            var box = auxContainer();
            var token = '<input type="hidden" name="_token" value="' + esc(csrf()) + '">';

            var holder = document.createElement('div');
            holder.innerHTML =
                '<form id="cap-' + d.id + '" action="' + esc(d.update_url) + '" method="POST" data-caption-ajax>' + token +
                    '<input type="hidden" name="_method" value="PUT"></form>' +
                '<form id="feat-' + d.id + '" action="' + esc(d.feature_url) + '" method="POST" data-featured-ajax>' + token +
                    '<input type="hidden" name="image_id" value="' + d.id + '"></form>' +
                '<form id="del-' + d.id + '" action="' + esc(d.delete_url) + '" method="POST" data-delete-ajax' +
                    ' data-confirm="' + esc(CFG.deleteImageLabel || 'ছবিটি মুছে ফেলবেন?') + '">' + token +
                    '<input type="hidden" name="_method" value="DELETE"></form>';

            while (holder.firstChild) box.appendChild(holder.firstChild);
        }
        renumber();
        syncOrder();
    })();

    /* ==================== ৭. ফর্ম সাবমিট / ডার্টি গার্ড ==================== */
    (function submitHandling() {
        var dirty = false;
        var submitting = false;

        form.addEventListener('input', function () { dirty = true; });
        form.addEventListener('change', function () { dirty = true; });

        // কনটেন্ট খালি থাকা অবস্থায় টুলবার দিয়ে যোগ করা শর্টকোডের ক্ষেত্রে ওয়ার্নিং
        form.addEventListener('submit', function (e) {
            submitting = true;
            dirty = false;

            /* চাপা বোতাম অনুযায়ী স্ট্যাটাস ড্রপডাউন সিঙ্ক — রিলোডের পর সঠিক স্ট্যাটাস দেখা যাবে */
            var pressed = e.submitter || null;
            var intent = pressed ? (pressed.getAttribute('data-submit-action') || pressed.value) : '';
            var intentMap = { save_draft: 'draft', draft: 'draft', pending: 'pending', publish: 'published', published: 'published', schedule: 'scheduled', scheduled: 'scheduled', archive: 'archived' };
            var statusSelect = qs('#status');
            if (statusSelect && intentMap[intent]) statusSelect.value = intentMap[intent];

            var content = qs('#content');
            if (content && content.value.indexOf('{{media:') !== -1 && !content.checkValidity()) {
                toast('সংবাদের বিস্তারিত অংশ লিখুন (ছবি ছাড়াও কিছু লেখা থাকতে হবে)', false);
            }

            // সাবমিট বাটনগুলো নিষ্ক্রিয় করে ডাবল সাবমিট রোধ
            qsa('[data-submit-action]').forEach(function (btn) {
                setTimeout(function () { btn.disabled = true; }, 0);
            });
        });

        window.addEventListener('beforeunload', function (e) {
            if (dirty && !submitting) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    })();
})();
