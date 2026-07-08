<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

it('renders @consent blocks only with consent', function () {
    Route::middleware('web')->get('/_gated', fn () => Blade::render(
        "@consent('analytics')\nGATED-CONTENT\n@endconsent"
    ));

    expect($this->get('/_gated')->getContent())->not->toContain('GATED-CONTENT');

    $withConsent = $this->withUnencryptedCookie('cc_cookie', json_encode(['categories' => ['analytics']]))
        ->get('/_gated');

    expect($withConsent->getContent())->toContain('GATED-CONTENT');
});

it('blocks routes behind consent middleware', function () {
    Route::middleware(['web', 'consent:analytics'])->get('/_protected', fn () => 'OK');

    $this->get('/_protected')->assertForbidden();

    $this->withUnencryptedCookie('cc_cookie', json_encode(['categories' => ['analytics']]))
        ->get('/_protected')
        ->assertOk();
});
