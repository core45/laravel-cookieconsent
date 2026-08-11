<?php

namespace Core45\CookieConsent\Support;

use Illuminate\Support\Facades\Lang;

class PreferencesButton
{
    /**
     * Attributes the button owns; user-supplied values for these are ignored so
     * the trigger can never be silently detached from the preferences modal.
     *
     * @var list<string>
     */
    protected const RESERVED = ['data-cc', 'type'];

    protected const LABEL_KEY = 'cookieconsent::cookieconsent.consentModal.showPreferencesBtn';

    /** @param array<string, string|bool|int|null> $attributes */
    public static function render(?string $label = null, array $attributes = []): string
    {
        // Sites that only offer accept-all/reject-all omit showPreferencesBtn
        // from their lang file — and those are precisely the sites that reach
        // the modal solely through this trigger. Without the has() check,
        // trans() would render the missing key itself as the button label.
        $label ??= Lang::has(self::LABEL_KEY)
            ? (string) trans(self::LABEL_KEY)
            : 'Manage preferences';

        $html = '<button type="button" data-cc="show-preferencesModal"';

        foreach ($attributes as $name => $value) {
            $name = strtolower((string) $name);

            // Inline event handlers are rejected outright: they would be blocked
            // by the nonce-based CSP this package supports, so allowing them
            // would only produce triggers that silently do nothing.
            if (in_array($name, self::RESERVED, true) || str_starts_with($name, 'on')) {
                continue;
            }

            // The D modifier matters: without it PCRE's `$` also matches before
            // a trailing newline, so "data-cc\n" would clear this check while
            // missing the RESERVED comparison above and emit a duplicate
            // data-cc attribute.
            if (! preg_match('/^[a-z_:][a-z0-9_:.-]*$/D', $name)) {
                continue;
            }

            if ($value === false || $value === null) {
                continue;
            }

            $html .= $value === true
                ? ' '.$name
                : ' '.$name.'="'.e((string) $value).'"';
        }

        return $html.'>'.e($label).'</button>';
    }
}
