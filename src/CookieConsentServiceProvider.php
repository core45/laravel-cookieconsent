<?php

namespace Core45\CookieConsent;

use Core45\CookieConsent\Console\ExportConsentCommand;
use Core45\CookieConsent\Console\PruneConsentCommand;
use Core45\CookieConsent\Http\LogConsentController;
use Core45\CookieConsent\Http\Middleware\RequireConsent;
use Core45\CookieConsent\Support\ConfigBuilder;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CookieConsentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cookieconsent.php', 'cookieconsent');
        $this->app->singleton(ConfigBuilder::class);
        $this->app->singleton(CookieConsentManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/cookieconsent.php' => config_path('cookieconsent.php'),
        ], 'cookieconsent-config');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cookieconsent');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/cookieconsent'),
        ], 'cookieconsent-lang');

        $this->publishes([
            __DIR__.'/../resources/dist/cookieconsent' => public_path('vendor/cookieconsent'),
            __DIR__.'/../resources/dist/iframemanager' => public_path('vendor/iframemanager'),
        ], 'cookieconsent-assets');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cookieconsent');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/cookieconsent'),
        ], 'cookieconsent-views');

        Blade::directive('cookieconsent', function () {
            return "<?php echo app(\Core45\CookieConsent\Support\ScriptsRenderer::class)->render(); ?>";
        });

        EncryptCookies::except(
            (string) config('cookieconsent.cookie.name', 'cc_cookie')
        );

        Blade::directive('consent', function (string $expression) {
            return "<?php if (app(\Core45\CookieConsent\Support\Consent::class)->has({$expression})): ?>";
        });

        Blade::directive('endconsent', fn () => '<?php endif; ?>');

        Blade::directive('cookiescript', function (string $expression) {
            return "<?php echo \Core45\CookieConsent\Support\ScriptTag::open({$expression}); ?>";
        });

        Blade::directive('endcookiescript', fn () => '</script>');

        Blade::directive('iframemanager', function () {
            return "<?php echo app(\Core45\CookieConsent\Support\IframemanagerRenderer::class)->render(); ?>";
        });

        Blade::directive('iframe', function (string $expression) {
            return "<?php echo \Core45\CookieConsent\Support\IframeTag::render({$expression}); ?>";
        });

        $this->app['router']->aliasMiddleware('consent', RequireConsent::class);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'cookieconsent-migrations');

        $logRoute = Route::middleware([
            'web',
            'throttle:'.config('cookieconsent.logging.rate_limit', '30,1'),
        ])
            ->post('cookie-consent/log', LogConsentController::class)
            ->name('cookieconsent.log');

        // The `web` group always applies CSRF validation. `logging.csrf` only
        // controls whether the client sends a token (see ScriptsRenderer), so
        // when it's false the route must also skip CSRF validation itself —
        // otherwise every consent POST 419s. Laravel 13 renamed the `web`
        // group's CSRF middleware to PreventRequestForgery and made
        // ValidateCsrfToken an empty deprecated subclass of it, so excluding
        // only the child class is a no-op on 13 (Router::resolveMiddleware
        // only strips a class that's excluded outright or is a *subclass* of
        // an excluded entry — never a parent). Excluding both keeps this
        // working across the framework versions this package supports.
        if (! config('cookieconsent.logging.csrf', true)) {
            $logRoute->withoutMiddleware([ValidateCsrfToken::class, PreventRequestForgery::class]);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportConsentCommand::class,
                PruneConsentCommand::class,
            ]);
        }
    }
}
