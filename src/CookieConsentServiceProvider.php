<?php

namespace Core45\CookieConsent;

use Core45\CookieConsent\Http\Middleware\RequireConsent;
use Core45\CookieConsent\Support\ConfigBuilder;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Blade;
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

        $this->app['router']->aliasMiddleware('consent', RequireConsent::class);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'cookieconsent-migrations');
    }
}
