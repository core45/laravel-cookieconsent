<?php

use Core45\CookieConsent\Models\ConsentLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

/**
 * Records what every CookieConsent getter answers at parse time, after a
 * known cc_cookie has been written, so the stub can be compared with the real
 * library before run().
 */
const PROBE_GETTERS = <<<'JS'
window.__isStub = window.CookieConsent.run.toString().indexOf('_ccRun') === -1;
document.cookie = 'cc_cookie=' + encodeURIComponent(JSON.stringify({ categories: ['necessary', 'analytics'], revision: 0, data: null, consentId: 'probe-id', services: {}, languageCode: 'en' })) + '; path=/';
window.__answers = JSON.stringify([
    window.CookieConsent.acceptedCategory('analytics'),
    window.CookieConsent.acceptedService('ga', 'analytics'),
    window.CookieConsent.validConsent(),
    window.CookieConsent.getUserPreferences(),
    window.CookieConsent.getCookie(),
    window.CookieConsent.getCookie('consentId'),
    window.CookieConsent.getCookie(undefined, 'missing_cookie'),
    window.CookieConsent.validCookie('cc_cookie'),
    window.CookieConsent.validCookie('missing_cookie'),
    window.CookieConsent.getConfig(),
    window.CookieConsent.getConfig('mode')
]);
JS;

beforeEach(function () {
    $this->artisan('vendor:publish', ['--tag' => 'cookieconsent-assets', '--force' => true]);

    config(['cookieconsent.hideFromBots' => false, 'cookieconsent.defer_assets' => true]);

    Route::get('/early.js', fn () => response('window.__earlyScriptRan = true;', 200, ['Content-Type' => 'text/javascript']));

    // The module script sits before @cookieconsent, as @vite does in a typical
    // layout: module scripts run in document order with deferred scripts, so
    // it runs while window.CookieConsent is still the stub.
    Route::middleware('web')->get('/deferred', function () {
        return Blade::render(<<<'BLADE'
            <!doctype html>
            <html lang="en">
            <head>
                <title>Demo</title>
                <script type="module">
                    window.__early = {
                        type: typeof window.CookieConsent,
                        accepted: window.CookieConsent.acceptedCategory('analytics'),
                        valid: window.CookieConsent.validConsent()
                    };
                    window.__captured = window.CookieConsent;
                    window.__loadScript = window.CookieConsent.loadScript('/early.js');
                </script>
                @cookieconsent
            </head>
            <body>
                <h1>Demo page</h1>
                @cookiescript('analytics')document.title = 'analytics-ran';@endcookiescript
                @cookiepreferences('Cookie settings')
            </body>
            </html>
        BLADE);
    });

    // Registered after @cookieconsent, so it runs after the package's own
    // DOMContentLoaded handler, like most page code.
    Route::middleware('web')->get('/returning', function () {
        return Blade::render(<<<'BLADE'
            <!doctype html>
            <html lang="en">
            <head>
                <title>Returning</title>
                <script>
                    var now = new Date().toISOString();
                    document.cookie = 'cc_cookie=' + encodeURIComponent(JSON.stringify({
                        categories: ['necessary', 'analytics'], revision: 0, data: null,
                        consentTimestamp: now, consentId: 'returning-visitor', services: { necessary: [], analytics: [] },
                        languageCode: 'en', lastConsentTimestamp: now, expirationTime: Date.now() + 86400000
                    })) + '; path=/';
                </script>
                @cookieconsent
            </head>
            <body>
                <h1>Returning visitor</h1>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        window.__onReady = [window.CookieConsent.validConsent(), window.CookieConsent.acceptedCategory('analytics')];
                    });
                </script>
            </body>
            </html>
        BLADE);
    });

    // Records the banner container's state on the first animation frame in
    // which the banner exists, i.e. what the first paint of it would show.
    Route::middleware('web')->get('/slow-css', function () {
        return Blade::render(<<<'BLADE'
            <!doctype html>
            <html lang="en">
            <head>
                <title>Slow CSS</title>
                <script>
                    (function poll() {
                        var main = document.getElementById('cc-main');
                        if (main && main.querySelector('.cm')) {
                            window.__firstFrame = {
                                display: getComputedStyle(main).display,
                                media: document.getElementById('cookieconsent-stylesheet').media
                            };
                            return;
                        }
                        requestAnimationFrame(poll);
                    })();
                </script>
                @cookieconsent
            </head>
            <body><h1>Slow CSS</h1><div id="app"></div></body>
            </html>
        BLADE);
    });

    Route::middleware('web')->get('/probe', function () {
        return Blade::render(
            "<!doctype html><html lang=\"en\"><head><title>Probe</title>@push('cookieconsent:config')<script>".PROBE_GETTERS.'</script>@endpush @cookieconsent</head><body>Probe</body></html>'
        );
    });
});

it('shows the banner, accepts, hides it, and writes a consent log', function () {
    $page = visit('/deferred');

    $page->assertSee('We use cookies')
        ->click('Accept all')
        ->assertDontSee('We use cookies');

    expect(ConsentLog::count())->toBe(1)
        ->and(ConsentLog::sole()->accepted_categories)->toContain('analytics');
});

it('keeps gated scripts inert until consent', function () {
    $page = visit('/deferred');

    $page->assertTitle('Demo');

    $page->click('Accept all');

    $page->assertTitle('analytics-ran');
});

it('lets the visitor reopen preferences after answering the banner', function () {
    $page = visit('/deferred');

    $page->click('Accept all')
        ->assertDontSee('Cookie preferences')
        ->click('Cookie settings')
        ->assertSee('Cookie preferences');
});

it('answers calls made before the library loads', function () {
    $page = visit('/deferred');

    expect($page->script('() => window.__early'))->toBe(['type' => 'object', 'accepted' => false, 'valid' => false])
        ->and($page->script('() => window.__loadScript'))->toBeTrue()
        ->and($page->script('() => window.__earlyScriptRan'))->toBeTrue();
});

it('forwards a reference captured before load to the real library', function () {
    $page = visit('/deferred');

    $page->click('Accept all')->assertDontSee('We use cookies');

    expect($page->script('() => window.__captured !== window.CookieConsent'))->toBeTrue()
        ->and($page->script("() => window.__captured.acceptedCategory('analytics')"))->toBeTrue()
        ->and($page->script("() => typeof window.CookieConsent.getConfig('categories')"))->toBe('object');
});

it('gives DOMContentLoaded handlers the stored consent, as without the flag', function () {
    config(['cookieconsent.defer_assets' => false]);
    $blocking = visit('/returning')->script('() => window.__onReady');

    config(['cookieconsent.defer_assets' => true]);
    $deferred = visit('/returning')->script('() => window.__onReady');

    expect($blocking)->toBe([true, true])
        ->and($deferred)->toBe($blocking);
});

it('hides the banner until a slow stylesheet arrives, then restores focus', function (?string $root) {
    $stylesheet = public_path('vendor/cookieconsent/cookieconsent.css');
    $css = file_get_contents($stylesheet);
    unlink($stylesheet);

    Route::get('/vendor/cookieconsent/cookieconsent.css', function () use ($css) {
        usleep(1_000_000);

        return response($css, 200, ['Content-Type' => 'text/css']);
    });

    if ($root !== null) {
        config(['cookieconsent.root' => $root]);
    }

    $page = visit('/slow-css');

    // Resolves once the container is shown again, plus the library's own
    // 100 ms focus delay, so the focus check below is not racing it.
    $revealed = $page->script(<<<'JS'
        () => new Promise(function (resolve) {
            (function check() {
                var main = document.getElementById('cc-main');
                if (main && getComputedStyle(main).display !== 'none' && document.getElementById('cookieconsent-stylesheet').media === 'all') {
                    setTimeout(function () { resolve(main.parentElement.id || main.parentElement.tagName); }, 200);
                    return;
                }
                setTimeout(check, 50);
            })();
        })
    JS);

    expect($page->script('() => window.__firstFrame'))->toBe(['display' => 'none', 'media' => 'print'])
        ->and($revealed)->toBe($root === null ? 'BODY' : 'app')
        ->and($page->script("() => getComputedStyle(document.getElementById('cc-main')).position"))->toBe('fixed')
        ->and($page->script("() => document.activeElement.matches('#cc-main .cm[tabindex=\"-1\"]')"))->toBeTrue();
})->with(['body' => [null], 'custom root' => ['#app']]);

it('focuses the same element as the library when the stylesheet is fast', function () {
    $page = visit('/deferred');

    $page->assertSee('We use cookies');

    $focused = $page->script(<<<'JS'
        () => new Promise(function (resolve) {
            setTimeout(function () { resolve(document.activeElement.matches('#cc-main .cm[tabindex="-1"]')); }, 300);
        })
    JS);

    expect($focused)->toBeTrue();
});

it('matches the element the library focuses in the preferences modal', function () {
    $page = visit('/deferred');

    $page->click('Cookie settings')->assertSee('Cookie preferences');

    $focused = $page->script(<<<'JS'
        () => new Promise(function (resolve) {
            setTimeout(function () { resolve(document.activeElement.matches('#cc-main .pm__title[tabindex="-1"]')); }, 300);
        })
    JS);

    expect($focused)->toBeTrue();
});

it('switches the stylesheet to all media once it loads', function () {
    $page = visit('/deferred');

    $page->assertSee('We use cookies');

    expect($page->script("() => document.getElementById('cookieconsent-stylesheet').media"))->toBe('all')
        ->and($page->script("() => getComputedStyle(document.getElementById('cc-main')).position"))->not->toBe('static');
});

it('gives the same pre-run answers as the blocking library', function () {
    // Both visits share one browser page, so read each result before the next visit.
    config(['cookieconsent.defer_assets' => false]);
    $page = visit('/probe');
    $blocking = ['isStub' => $page->script('() => window.__isStub'), 'answers' => $page->script('() => window.__answers')];

    config(['cookieconsent.defer_assets' => true]);
    $page = visit('/probe');
    $deferred = ['isStub' => $page->script('() => window.__isStub'), 'answers' => $page->script('() => window.__answers')];

    expect($blocking['isStub'])->toBeFalse()
        ->and($deferred['isStub'])->toBeTrue()
        ->and($deferred['answers'])->toBeString()->toBe($blocking['answers']);
});
