<link rel="stylesheet" href="{{ asset('vendor/cookieconsent/cookieconsent.css') }}">
<script{!! $nonceAttr !!} src="{{ asset('vendor/cookieconsent/cookieconsent.umd.js') }}"></script>
<script{!! $nonceAttr !!}>
let config = (function revive(node) {
    if (Array.isArray(node)) {
        return node.map(revive);
    }
    if (node && typeof node === 'object') {
        if (typeof node.__regex__ === 'string') {
            return new RegExp(node.__regex__, node.flags || '');
        }
        for (const key of Object.keys(node)) {
            node[key] = revive(node[key]);
        }
    }
    return node;
})({{ \Illuminate\Support\Js::from($config) }});
@if ($loggingEnabled)
(function () {
    const logUrl = {{ \Illuminate\Support\Js::from($logUrl) }};
    const csrfToken = {{ \Illuminate\Support\Js::from($csrfToken) }};
    let pending = null;

    const makeIdempotencyKey = function (consentId, revision, action) {
        // Kept well under the 64-char column/validation limit: a full UUID
        // consentId plus a full crypto.randomUUID() would overflow it, so
        // both are shortened while staying unique per event.
        const rand = (window.crypto && window.crypto.randomUUID)
            ? window.crypto.randomUUID().replace(/-/g, '').slice(0, 10)
            : Math.random().toString(36).slice(2, 12);

        return (consentId || '').slice(0, 12) + '.' + revision + '.' + action.slice(0, 13) + '.' + Date.now().toString(36) + '.' + rand;
    };

    const sendBeacon = function () {
        if (pending === null || !navigator.sendBeacon) {
            return;
        }
        const blob = new Blob([JSON.stringify(pending.body)], { type: 'application/json' });
        if (navigator.sendBeacon(logUrl, blob)) {
            pending = null;
        }
    };

    window.addEventListener('pagehide', sendBeacon);
    window.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            sendBeacon();
        }
    });

    const attemptSend = function (body, attempt) {
        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        @if ($csrfToken !== null)
        headers['X-CSRF-TOKEN'] = csrfToken;
        @endif

        fetch(logUrl, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(body)
        }).then(function (response) {
            if (response.ok) {
                if (pending && pending.body.idempotencyKey === body.idempotencyKey) {
                    pending = null;
                }
                return;
            }
            if ((response.status === 429 || response.status >= 500) && attempt < 2) {
                setTimeout(function () { attemptSend(body, attempt + 1); }, attempt === 0 ? 1000 : 3000);
                return;
            }
            if (window.console && console.warn) {
                console.warn('cookieconsent: failed to log consent', response.status);
            }
        }).catch(function (e) {
            if (attempt < 2) {
                setTimeout(function () { attemptSend(body, attempt + 1); }, attempt === 0 ? 1000 : 3000);
                return;
            }
            if (window.console && console.warn) {
                console.warn('cookieconsent: failed to log consent', e);
            }
        });
    };

    const logConsent = function (action) {
        const cookie = window.CookieConsent.getCookie();
        const preferences = window.CookieConsent.getUserPreferences();
        const body = {
            action: action,
            consentId: cookie.consentId,
            revision: cookie.revision,
            acceptType: preferences.acceptType,
            acceptedCategories: preferences.acceptedCategories,
            rejectedCategories: preferences.rejectedCategories,
            acceptedServices: preferences.acceptedServices,
            languageCode: cookie.languageCode,
            payload: cookie,
            idempotencyKey: makeIdempotencyKey(cookie.consentId, cookie.revision, action),
            @if ($csrfToken !== null)
            _token: csrfToken
            @endif
        };
        pending = { body: body };
        attemptSend(body, 0);
    };
    window.addEventListener('cc:onFirstConsent', function () { logConsent('first_consent'); });
    window.addEventListener('cc:onChange', function () { logConsent('change'); });
})();
@endif
</script>
@stack('cookieconsent:config')
<script{!! $nonceAttr !!}>
(function () {
    var start = function () { window.CookieConsent.run(config); };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
