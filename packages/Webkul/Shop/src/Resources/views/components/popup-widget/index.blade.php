@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

@php
    // Ensure deterministic popup selection (highest sort_order / latest).
    $popup = \Webkul\Theme\Models\ThemeCustomization::query()
        ->where('type', 'popup_widget')
        ->where('status', 1)
        ->where('channel_id', core()->getCurrentChannel()->id)
        ->orderByDesc('sort_order')
        ->orderByDesc('id')
        ->first();

    $options = $popup?->options ?? [];

    $isEnabled = (bool) ($options['enabled'] ?? false);
    $contentType = $options['content_type'] ?? 'image';

    $now = now();

    $startAt = ! empty($options['start_at']) ? \Illuminate\Support\Carbon::parse($options['start_at']) : null;
    $endAt = ! empty($options['end_at']) ? \Illuminate\Support\Carbon::parse($options['end_at']) : null;

    $withinWindow = (! $startAt || $now->greaterThanOrEqualTo($startAt)) && (! $endAt || $now->lessThanOrEqualTo($endAt));

    $displayOn = $options['display_on'] ?? 'all';
    $path = '/'.ltrim(request()->path(), '/');
    $shouldShowOnPage = true;

    if ($displayOn === 'home') {
        // Laravel returns '' for '/', but be defensive.
        $shouldShowOnPage = request()->path() === '' || request()->path() === '/'
            || (request()->route() && request()->route()->named('shop.home.index'));
    } elseif ($displayOn === 'urls') {
        $urlsRaw = (string) ($options['urls'] ?? '');
        $urls = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $urlsRaw))));

        $shouldShowOnPage = in_array($path, $urls, true) || in_array(rtrim($path, '/'), $urls, true);
    }

    $canRender = $isEnabled && $withinWindow && $shouldShowOnPage;

    // Use a stable key per popup id; versioning is handled in JS.
    $popupKey = $popup ? 'popup_widget_'.$popup->id : null;
    $popupVersion = $popup?->updated_at?->timestamp;

    // Use cookie-safe keys (no ':' characters).
    $cookieKeySeen = $popupKey ? 'bagisto_'.$popupKey.'_seen_at' : null;
    $cookieKeyNever = $popupKey ? 'bagisto_'.$popupKey.'_never' : null;

    $cookieNever = $cookieKeyNever ? request()->cookie($cookieKeyNever) : null;
    $cookieSeenRaw = $cookieKeySeen ? request()->cookie($cookieKeySeen) : null;

    $cookieSeenAt = 0;
    if (is_string($cookieSeenRaw) && preg_match('/^(\d+):(\d+)$/', $cookieSeenRaw, $m)) {
        $v = (int) $m[1];
        $t = (int) $m[2];

        if (! empty($popupVersion) && $v === (int) $popupVersion && $t > 0) {
            $cookieSeenAt = $t;
        }
    }

    // Server-side suppression based on cookie (covers daily/weekly/once reliably).
    $frequency = $options['frequency'] ?? 'session';
    $nowMs = (int) round(microtime(true) * 1000);
    $msDay = 24 * 60 * 60 * 1000;
    $msWeek = 7 * $msDay;

    $cookieBlocksPopup = false;
    if ($cookieNever === '1') {
        $cookieBlocksPopup = true;
    } elseif ($cookieSeenAt > 0) {
        if ($frequency === 'once') $cookieBlocksPopup = true;
        if ($frequency === 'daily' && ($nowMs - $cookieSeenAt) < $msDay) $cookieBlocksPopup = true;
        if ($frequency === 'weekly' && ($nowMs - $cookieSeenAt) < $msWeek) $cookieBlocksPopup = true;
    }

    // If we are going to render the popup, queue a server-side cookie immediately.
    // This makes frequency controls reliable even when the browser blocks JS storage.
    if ($canRender && $popupKey && ! $cookieBlocksPopup && $cookieKeySeen) {
        $version = (int) ($popupVersion ?? 0);
        $seenValue = $version.':'.$nowMs;

        // Lifetime in minutes.
        $minutes = 60 * 24; // default 1 day
        if ($frequency === 'weekly') $minutes = 60 * 24 * 7;
        if ($frequency === 'once') $minutes = 60 * 24 * 365 * 5;
        if ($frequency === 'session') $minutes = 0; // session cookie

        try {
            \Illuminate\Support\Facades\Cookie::queue($cookieKeySeen, $seenValue, $minutes, '/', null, false, false, false, 'lax');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    // Fast, reliable rendering for custom HTML/CSS: use an iframe srcdoc to avoid
    // CSS/DOM interference from the storefront (and Vue mounting).
    $popupCss = is_string($options['css'] ?? null)
        ? preg_replace('/^\s*<style\b[^>]*>|<\/style>\s*$/i', '', $options['css'])
        : '';

    $popupHtml = is_string($options['html'] ?? null) ? $options['html'] : '';

    // If the user pasted <style> inside HTML, extract it so it works even when
    // they don't split HTML/CSS perfectly.
    if (preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $popupHtml, $m)) {
        $inlineCss = implode("\n", $m[1]);
        $popupCss = trim($popupCss."\n".$inlineCss);

        $popupHtml = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $popupHtml);
    }

    // Only show one close button: we provide it outside the iframe.
    $popupCss = trim($popupCss."\n".".promo-close{display:none!important;}\n");

    $popupSrcdoc = trim((string) ("<!doctype html><html><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><style>\n{$popupCss}\n</style></head><body>".$popupHtml."</body></html>"));
    $popupSrcdocB64 = base64_encode($popupSrcdoc);

@endphp

@if ($canRender && $popupKey && ! $cookieBlocksPopup)
    @if ($contentType === 'html')
        <div
            id="promo-popup-frame-root"
            style="display:none; position:fixed; inset:0; z-index:99999;"
        >
            <div
                id="promo-popup-frame-overlay"
                style="position:absolute; inset:0; background: transparent;"
                onclick="var r=document.getElementById('promo-popup-frame-root'); if(r) r.style.display='none'; var f=document.getElementById('promo-popup-frame'); if(f) f.srcdoc='<!doctype html><html><body></body></html>';"
            ></div>

            <div style="position:relative; z-index:1; min-height:100%; display:flex; align-items:center; justify-content:center; padding:16px;">
                <div style="position:relative; width:100%; max-width:760px;">
                    <button
                        type="button"
                        id="promo-popup-frame-close"
                        style="position:absolute; right:10px; top:10px; z-index:2; font-size:28px; line-height:1; background:rgba(0,0,0,.25); border:0; color:#fff; border-radius:999px; width:44px; height:44px; cursor:pointer;"
                        aria-label="Close popup"
                        onclick="var r=document.getElementById('promo-popup-frame-root'); if(r) r.style.display='none'; var f=document.getElementById('promo-popup-frame'); if(f) f.srcdoc='<!doctype html><html><body></body></html>';"
                    >
                        ×
                    </button>

                    <iframe
                        id="promo-popup-frame"
                        title="Promotion Popup"
                        style="display:block; width:100%; height:80vh; max-height:720px; border:0; border-radius:0; background:transparent; position:relative; z-index:0;"
                        sandbox="allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox"
                        data-srcdoc-b64="{{ $popupSrcdocB64 }}"
                    ></iframe>
                </div>
            </div>
        </div>
    @else
        <div id="promo-popup-simple-root" style="display:none; position:fixed; inset:0; z-index:99999;">
            <div
                id="promo-popup-simple-overlay"
                style="position:absolute; inset:0; background:rgba(0,0,0,.55);"
                onclick="var r=document.getElementById('promo-popup-simple-root'); if(r) r.style.display='none';"
            ></div>

            <div style="position:relative; z-index:1; min-height:100%; display:flex; align-items:center; justify-content:center; padding:16px;">
                <div style="position:relative; width:100%; max-width:760px; background:#fff; border-radius:16px; padding:16px;">
                    <button
                        type="button"
                        id="promo-popup-simple-close"
                        style="position:absolute; right:10px; top:10px; z-index:2; font-size:28px; line-height:1; background:rgba(0,0,0,.18); border:0; color:#111; border-radius:999px; width:44px; height:44px; cursor:pointer;"
                        aria-label="Close popup"
                        onclick="var r=document.getElementById('promo-popup-simple-root'); if(r) r.style.display='none';"
                    >
                        ×
                    </button>

                    <div class="grid gap-4">
                        @if ($contentType === 'image' && ! empty($options['banner']))
                            <div class="grid gap-3">
                                @if (! empty($options['link_url']))
                                    <a href="{{ $options['link_url'] }}">
                                        <img src="/{{ $options['banner'] }}" alt="Popup" class="h-auto w-full rounded" />
                                    </a>
                                @else
                                    <img src="/{{ $options['banner'] }}" alt="Popup" class="h-auto w-full rounded" />
                                @endif

                                @if (! empty($options['link_text']) && ! empty($options['link_url']))
                                    <a href="{{ $options['link_url'] }}" class="inline-flex items-center justify-center rounded-lg bg-navyBlue px-4 py-2 text-white hover:opacity-90">
                                        {{ $options['link_text'] }}
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="text-sm text-zinc-600">
                                @if (! empty($options['link_text']) && ! empty($options['link_url']))
                                    <a href="{{ $options['link_url'] }}" class="inline-flex items-center justify-center rounded-lg bg-navyBlue px-4 py-2 text-white hover:opacity-90">
                                        {{ $options['link_text'] }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if ((bool) ($options['show_no_show_again'] ?? true))
                            <label class="flex items-center gap-2 text-sm text-zinc-700">
                                <input type="checkbox" id="promo-popup-never" />
                                <span>@lang('shop::app.components.layouts.popup-widget.dont-show-again')</span>
                            </label>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @pushOnce('scripts')
        <script>
            (function () {
                const popupKey = @json($popupKey);
                const popupVersion = parseInt(@json($popupVersion), 10) || 0;
                const frequency = @json($options['frequency'] ?? 'session');
                const autoCloseSeconds = parseInt(@json($options['auto_close_seconds'] ?? ''), 10);
                const overlayClickClose = @json((string) ($options['overlay_click_close'] ?? '1')) === '1';
                const isHtmlMode = @json($contentType) === 'html';

                // Mirror server-side cookie keys for reliability.
                const storageKeySeen = `bagisto:${popupKey}:seen_at`;
                const storageKeyNever = `bagisto:${popupKey}:never`;

                // Cookie-safe keys used for server-side suppression.
                const cookieKeySeen = @json($cookieKeySeen);
                const cookieKeyNever = @json($cookieKeyNever);

                const parseSeen = (raw) => {
                    if (!raw) return 0;

                    // Format: "<version>:<ms>". If version mismatches, treat as unseen.
                    const m = String(raw).match(/^(\d+):(\d+)$/);
                    if (!m) return 0;

                    const v = parseInt(m[1], 10);
                    const t = parseInt(m[2], 10);

                    if (!Number.isFinite(v) || !Number.isFinite(t)) return 0;
                    if (popupVersion && v !== popupVersion) return 0;

                    return t;
                };

                const formatSeen = (ms) => {
                    const v = popupVersion || 0;
                    return `${v}:${ms}`;
                };

                const cookieGet = (key) => {
                    const name = encodeURIComponent(key) + '=';
                    const parts = document.cookie.split(';');
                    for (let i = 0; i < parts.length; i++) {
                        const c = parts[i].trim();
                        if (c.startsWith(name)) return decodeURIComponent(c.slice(name.length));
                    }
                    return null;
                };

                const cookieSet = (key, value, days = 365) => {
                    const expires = new Date(Date.now() + days * 864e5).toUTCString();
                    document.cookie = `${encodeURIComponent(key)}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
                };

                const safeStorageGet = (storage, key) => {
                    try {
                        return storage.getItem(key);
                    } catch (e) {
                        return null;
                    }
                };

                const safeStorageSet = (storage, key, value) => {
                    try {
                        storage.setItem(key, value);
                        return true;
                    } catch (e) {
                        return false;
                    }
                };

                const DEBUG_POPUP = false;

                // Never flag (cookie first, then storage if available).
                const never = (
                    (cookieKeyNever ? cookieGet(cookieKeyNever) : null)
                    || safeStorageGet(localStorage, storageKeyNever)
                    || safeStorageGet(sessionStorage, storageKeyNever)
                );
                try {
                    if (never === '1') {
                        return;
                    }

                    const now = Date.now();

                    const getSeenAt = () => {
                        const fromCookie = parseSeen(cookieKeySeen ? cookieGet(cookieKeySeen) : null);
                        const fromLocal = parseSeen(safeStorageGet(localStorage, storageKeySeen));
                        const fromSession = parseSeen(safeStorageGet(sessionStorage, storageKeySeen));
                        return Math.max(fromCookie, fromLocal, fromSession);
                    };

                    const setSeenAt = () => {
                        if (frequency === 'session') {
                            safeStorageSet(sessionStorage, storageKeySeen, formatSeen(now));
                        } else {
                            safeStorageSet(localStorage, storageKeySeen, formatSeen(now));
                        }

                        // Best-effort cookie (might be blocked).
                        if (cookieKeySeen) {
                            cookieSet(cookieKeySeen, formatSeen(now), frequency === 'session' ? 1 : 365);
                        }

                        // Debug aid.
                        if (DEBUG_POPUP) {
                            console.log('[popup_widget] setSeenAt', {
                                frequency,
                                cookieKeySeen,
                                stored: formatSeen(now),
                                cookie: cookieKeySeen ? cookieGet(cookieKeySeen) : null,
                                local: safeStorageGet(localStorage, storageKeySeen),
                                session: safeStorageGet(sessionStorage, storageKeySeen),
                            });
                        }
                    };

                    const markDismissed = () => {
                        // Always prevent re-open during this session when user dismisses.
                        safeStorageSet(sessionStorage, storageKeySeen, formatSeen(now));

                        if (frequency !== 'session') {
                            safeStorageSet(localStorage, storageKeySeen, formatSeen(now));
                        }

                        if (cookieKeySeen) {
                            cookieSet(cookieKeySeen, formatSeen(now), frequency === 'session' ? 1 : 365);
                        }
                    };

                    const seenAt = getSeenAt();

                    const msDay = 24 * 60 * 60 * 1000;
                    const msWeek = 7 * msDay;

                    let shouldOpen = true;
                    if (frequency === 'session' && seenAt) shouldOpen = false;
                    if (frequency === 'once' && seenAt) shouldOpen = false;
                    if (frequency === 'daily' && (now - seenAt) < msDay) shouldOpen = false;
                    if (frequency === 'weekly' && (now - seenAt) < msWeek) shouldOpen = false;

                    if (!shouldOpen) {
                        if (DEBUG_POPUP) console.log('[popup_widget] blocked by frequency', { frequency, seenAt });
                        return;
                    }

                    // Mark as seen immediately to avoid double opens.
                    setSeenAt();

                    window.addEventListener('load', function () {
                        if (isHtmlMode) {
                            const root = document.getElementById('promo-popup-frame-root');
                            const frame = document.getElementById('promo-popup-frame');
                            const closeBtn = document.getElementById('promo-popup-frame-close');
                            const overlay = document.getElementById('promo-popup-frame-overlay');
                            if (DEBUG_POPUP) console.log('[popup_widget] html mode open', { hasFrame: !!frame, autoCloseSeconds });
                            const close = () => {
                                if (root) root.style.display = 'none';
                                if (frame) frame.srcdoc = '<!doctype html><html><body></body></html>';
                                markDismissed();
                            };

                            if (closeBtn) {
                                closeBtn.addEventListener('click', close);
                            }

                            if (overlayClickClose && overlay) {
                                overlay.addEventListener('click', close);
                            }

                            if (root) {
                                root.style.display = 'block';
                            }

                            if (frame) {
                                // Assign srcdoc at runtime to avoid HTML escaping issues.
                                const b64 = frame.dataset ? frame.dataset.srcdocB64 : null;

                                if (b64) {
                                    try {
                                        const raw = atob(b64);

                                        // UTF-8 safe decode.
                                        if (typeof TextDecoder !== 'undefined') {
                                            const bytes = Uint8Array.from(raw, c => c.charCodeAt(0));
                                            frame.srcdoc = new TextDecoder('utf-8').decode(bytes);
                                        } else {
                                            frame.srcdoc = raw;
                                        }
                                    } catch (e) {
                                        frame.srcdoc = '<!doctype html><html><body></body></html>';
                                    }
                                }
                            }

                            document.addEventListener('keydown', function (e) {
                                if (e && e.key === 'Escape') close();
                            }, { once: true });

                            if (Number.isFinite(autoCloseSeconds) && autoCloseSeconds > 0) {
                                setTimeout(close, autoCloseSeconds * 1000);
                            }

                            return;
                        }

                        const simpleRoot = document.getElementById('promo-popup-simple-root');
                        const simpleClose = document.getElementById('promo-popup-simple-close');
                        const simpleOverlay = document.getElementById('promo-popup-simple-overlay');

                        const closeSimple = () => {
                            if (simpleRoot) simpleRoot.style.display = 'none';
                            markDismissed();
                        };

                        if (simpleRoot) {
                            simpleRoot.style.display = 'block';
                        }

                        if (simpleClose) {
                            simpleClose.addEventListener('click', closeSimple);
                        }

                        if (overlayClickClose && simpleOverlay) {
                            simpleOverlay.addEventListener('click', closeSimple);
                        }

                        // Mark as dismissed when the modal is closed.
                        document.addEventListener('click', function (e) {
                            const t = e.target;
                            if (!t) return;

                            if (t.id === 'promo-popup-simple-close' || t.id === 'promo-popup-simple-overlay') markDismissed();
                        }, { capture: true });

                        // Bind optional behaviors.
                        // No-op for simple popup (we control overlay behavior directly).

                        const neverEl = document.getElementById('promo-popup-never');
                        if (neverEl) {
                            neverEl.addEventListener('change', function () {
                                if (neverEl.checked) {
                                    safeStorageSet(localStorage, storageKeyNever, '1');
                                    safeStorageSet(sessionStorage, storageKeyNever, '1');
                                    if (cookieKeyNever) cookieSet(cookieKeyNever, '1', 365);

                                    // Close immediately.
                                    if (isHtmlMode) {
                                        const frame = document.getElementById('promo-popup-frame');
                                        if (frame) frame.style.display = 'none';
                                    } else {
                                        closeSimple();
                                    }
                                }
                            });
                        }

                        if (Number.isFinite(autoCloseSeconds) && autoCloseSeconds > 0) {
                            setTimeout(function () {
                                // Auto-close should also count as dismissed.
                                closeSimple();
                            }, autoCloseSeconds * 1000);
                        }
                    });
                } catch (e) {
                    // If localStorage is blocked, just show it.
                }
            })();
        </script>
    @endPushOnce
@endif
