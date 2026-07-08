<?php

use Core45\CookieConsent\Tests\CsrfDisabledTestCase;
use Core45\CookieConsent\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class)->in('Unit', 'Feature');
uses(TestCase::class)->in('Browser');

// Separate from tests/Feature: needs `logging.csrf` set before boot, which
// requires a different base TestCase — see CsrfDisabledTestCase's docblock.
uses(CsrfDisabledTestCase::class, RefreshDatabase::class)->in('FeatureCsrfDisabled');
