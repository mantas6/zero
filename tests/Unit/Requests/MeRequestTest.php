<?php

use App\Http\Integrations\Toggl\Requests\MeRequest;

it('resolves the correct endpoint', function () {
    $request = new MeRequest;

    expect($request->resolveEndpoint())->toBe('/me');
});

it('uses GET method', function () {
    $request = new MeRequest;

    expect($request->getMethod()->value)->toBe('GET');
});
