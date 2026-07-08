<link rel="stylesheet" href="{{ asset('vendor/iframemanager/iframemanager.css') }}">
<script{!! $nonceAttr !!} src="{{ asset('vendor/iframemanager/iframemanager.js') }}"></script>
<script{!! $nonceAttr !!}>
let imConfig = {{ \Illuminate\Support\Js::from($imConfig) }};
</script>
@stack('cookieconsent:iframemanager-config')
<script{!! $nonceAttr !!}>
(function () {
    var start = function () {
        const im = iframemanager();
        im.run(imConfig);

        const categoryMap = {{ \Illuminate\Support\Js::from($categoryMap) }};

        const applyConsent = function () {
            for (const [service, category] of Object.entries(categoryMap)) {
                if (window.CookieConsent.acceptedCategory(category)) {
                    im.acceptService(service);
                } else {
                    im.rejectService(service);
                }
            }
        };

        window.addEventListener('cc:onConsent', applyConsent);
        window.addEventListener('cc:onChange', applyConsent);
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
