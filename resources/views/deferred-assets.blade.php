<link rel="stylesheet" href="{{ asset('vendor/cookieconsent/cookieconsent.css') }}" media="print" id="cookieconsent-stylesheet">
<script{!! $nonceAttr !!}>
(function () {
    if (window._ccDeferred) {
        return;
    }

    // Loads the stylesheet without blocking the first render: it arrives as a
    // print stylesheet and is switched to all media once loaded. Done here
    // rather than in an onload attribute, which a nonce-based CSP would block.
    var link = document.getElementById('cookieconsent-stylesheet');
    var stylesheetSettled = !link || !!link.sheet;
    var hiddenContainers = [];
    var observer = null;

    var settleStylesheet = function () {
        if (link) {
            link.media = 'all';
        }
        stylesheetSettled = true;
        if (observer) {
            observer.disconnect();
        }
        var revealed = hiddenContainers.splice(0);
        revealed.forEach(function (container) {
            container.style.display = '';
        });
        if (revealed.length) {
            restoreFocus();
        }
    };

    // The library moves focus into a modal shortly after showing it. That
    // focus is lost while the container is hidden, so it is repeated here.
    // cookieconsent 3.3 focuses the .cm box itself in the consent modal and
    // the .pm__title heading in the preferences modal.
    var restoreFocus = function () {
        if (document.activeElement && document.activeElement !== document.body) {
            return;
        }
        var state = document.documentElement.classList;
        var selector = state.contains('show--preferences')
            ? '#cc-main .pm__title[tabindex="-1"]'
            : (state.contains('show--consent') ? '#cc-main .cm[tabindex="-1"]' : null);
        var target = selector ? document.querySelector(selector) : null;
        if (target) {
            target.focus();
        }
    };

    if (stylesheetSettled) {
        settleStylesheet();
    } else {
        link.addEventListener('load', settleStylesheet);
        link.addEventListener('error', settleStylesheet);
    }

    // run() starts at DOMContentLoaded as it does without defer_assets. If the
    // stylesheet is still on its way, the #cc-main container the library adds
    // (to <body>, or to the configured `root`) is hidden until it arrives, so
    // it never paints unstyled. MutationObserver callbacks run before the next
    // paint, and setting element.style from script is allowed under a
    // nonce-based CSP. The observer disconnects once the stylesheet settles.
    var hideUntilStyled = function () {
        if (stylesheetSettled || !window.MutationObserver) {
            return;
        }
        observer = new MutationObserver(function (records) {
            records.forEach(function (record) {
                Array.prototype.forEach.call(record.addedNodes, function (node) {
                    if (node.id === 'cc-main' && !stylesheetSettled) {
                        node.style.display = 'none';
                        hiddenContainers.push(node);
                    }
                });
            });
        });
        observer.observe(document.documentElement, { childList: true, subtree: true });
    };

    var stub = {};
    var queue = [];
    var flushed = false;

    // cookieconsent.umd.js replaces window.CookieConsent with a new object,
    // so the library is whatever that property holds once it is not the stub.
    var library = function () {
        var current = window.CookieConsent;

        return current && current !== stub ? current : null;
    };

    var readCookie = function (name) {
        var match = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');

        return match ? match.pop() : '';
    };

    // The answers cookieconsent.umd.js itself gives before run(), so a call
    // made before the library loads sees what it would have seen without defer.
    var beforeRun = {
        acceptedCategory: function () {
            return false;
        },
        acceptedService: function () {
            return false;
        },
        validConsent: function () {
            return false;
        },
        getUserPreferences: function () {
            return { acceptType: '', acceptedCategories: [], rejectedCategories: [], acceptedServices: {}, rejectedServices: {} };
        },
        getCookie: function (field, cookieName) {
            var value = null;
            try {
                value = JSON.parse(decodeURIComponent(readCookie(cookieName || 'cc_cookie')));
            } catch (error) {
                value = null;
            }
            value = value || {};

            return field ? value[field] : value;
        },
        validCookie: function (cookieName) {
            return readCookie(cookieName) !== '';
        },
        getConfig: function (key) {
            var defaults = {
                mode: 'opt-in',
                revision: 0,
                autoShow: true,
                lazyHtmlGeneration: true,
                autoClearCookies: true,
                manageScriptTags: true,
                hideFromBots: true,
                respectGpc: false,
                cookie: { name: 'cc_cookie', expiresAfterDays: 182, domain: '', path: '/', secure: true, sameSite: 'Lax' }
            };

            // The library looks a key up with `||`, so a falsy default such as
            // revision 0 comes back undefined.
            return key ? defaults[key] || undefined : defaults;
        }
    };

    Object.keys(beforeRun).forEach(function (name) {
        stub[name] = function () {
            var cc = library();

            return cc ? cc[name].apply(cc, arguments) : beforeRun[name].apply(null, arguments);
        };
    });

    // Actions wait in the queue until flush(), which replays them in order the
    // moment the library has loaded, before run(). The library's own async
    // methods still return a Promise.
    var asyncActions = ['run', 'setLanguage', 'loadScript'];
    var syncActions = ['acceptCategory', 'acceptService', 'eraseCookies', 'hide', 'hidePreferences', 'reset', 'setCookieData', 'show', 'showPreferences'];

    asyncActions.concat(syncActions).forEach(function (name) {
        var isAsync = asyncActions.indexOf(name) !== -1;

        stub[name] = function () {
            var cc = library();
            if (flushed && cc) {
                return cc[name].apply(cc, arguments);
            }
            var call = { name: name, args: Array.prototype.slice.call(arguments), isAsync: isAsync };
            queue.push(call);
            if (isAsync) {
                return new Promise(function (resolve, reject) {
                    call.resolve = resolve;
                    call.reject = reject;
                });
            }

            return name === 'setCookieData' ? false : undefined;
        };
    });

    var flush = function () {
        var cc = library();
        if (!cc) {
            return false;
        }
        flushed = true;
        queue.splice(0).forEach(function (call) {
            try {
                var result = cc[call.name].apply(cc, call.args);
                if (call.isAsync) {
                    call.resolve(result);
                }
            } catch (error) {
                if (call.isAsync) {
                    call.reject(error);
                } else if (window.console && console.error) {
                    console.error(error);
                }
            }
        });

        return true;
    };

    // A script's load event fires straight after it has run and before the
    // next deferred script, so queued calls replay as soon as the library
    // exists. Captured on document because load does not bubble.
    document.addEventListener('load', function (event) {
        if (event.target && event.target.id === 'cookieconsent-library') {
            flush();
        }
    }, true);

    if (!window.CookieConsent) {
        window.CookieConsent = stub;
    }

    window._ccDeferred = { flush: flush, hideUntilStyled: hideUntilStyled };
})();
</script>
<script{!! $nonceAttr !!} defer src="{{ asset('vendor/cookieconsent/cookieconsent.umd.js') }}" id="cookieconsent-library"></script>
