<?php

declare(strict_types=1);

const JS_STRINGS = [
    'Preparing…',
    'Sent, opening…',
    'Server error',
    'Connection failed',
    'MB',
    'Loading data…',
    'Drawing land…',
    'Filling territory…',
    'Colouring…',
    'region',
    'military control',
    'cities',
    'population',
    'inactive',
    'Religions',
    'no data',
    'and %d more',
    'share',
    'side %d',
    'not a member',
    'troops',
    'units',
    'missiles',
    'selected country',
    'hostile',
    'allied',
    'of',
    'Pick a country…',
    'Pick…',
    'Languages',
    'regions',
];


function js_i18n(): array
{
    $out = [];
    foreach (JS_STRINGS as $k) $out[$k] = t($k);
    return $out;
}
