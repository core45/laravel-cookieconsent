<?php

use Core45\CookieConsent\Support\ConfigBuilder;

it('is deterministic and key-order invariant', function () {
    $a = ConfigBuilder::canonicalJson(['b' => 1, 'a' => ['d' => 2, 'c' => 3]]);
    $b = ConfigBuilder::canonicalJson(['a' => ['c' => 3, 'd' => 2], 'b' => 1]);

    expect($a)->toBe($b)->toBe('{"a":{"c":3,"d":2},"b":1}');
});

it('preserves list order while sorting assoc keys', function () {
    expect(ConfigBuilder::canonicalJson(['list' => ['z', 'a']]))->toBe('{"list":["z","a"]}');
});

it('rotates when config, translations, or policy_version change', function () {
    $builder = app(ConfigBuilder::class);
    $base = $builder->policyHash();

    config()->set('cookieconsent.guiOptions.consentModal.layout', 'cloud');
    $afterConfigChange = $builder->policyHash();

    config()->set('cookieconsent.logging.policy_version', 'v2');
    $afterVersionChange = $builder->policyHash();

    app()->setLocale('es');
    $afterLocaleChange = $builder->policyHash();

    expect($base)->toHaveLength(64)
        ->and($afterConfigChange)->not->toBe($base)
        ->and($afterVersionChange)->not->toBe($afterConfigChange)
        ->and($afterLocaleChange)->not->toBe($afterVersionChange);
});
