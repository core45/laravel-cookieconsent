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
    const logConsent = function (action) {
        const cookie = window.CookieConsent.getCookie();
        const preferences = window.CookieConsent.getUserPreferences();
        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        @if ($csrfToken !== null)
        headers['X-CSRF-TOKEN'] = {{ \Illuminate\Support\Js::from($csrfToken) }};
        @endif
        fetch({{ \Illuminate\Support\Js::from($logUrl) }}, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                action: action,
                consentId: cookie.consentId,
                revision: cookie.revision,
                acceptType: preferences.acceptType,
                acceptedCategories: preferences.acceptedCategories,
                rejectedCategories: preferences.rejectedCategories,
                acceptedServices: preferences.acceptedServices,
                languageCode: cookie.languageCode,
                payload: cookie
            })
        });
    };
    window.addEventListener('cc:onFirstConsent', function () { logConsent('first_consent'); });
    window.addEventListener('cc:onChange', function () { logConsent('change'); });
})();
@endif
</script>
@stack('cookieconsent:config')
<script{!! $nonceAttr !!}>window.CookieConsent.run(config);</script>
