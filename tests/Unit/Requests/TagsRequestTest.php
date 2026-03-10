<?php

use App\Http\Integrations\Toggl\Requests\TagsRequest;

it('resolves the correct endpoint', function () {
    $request = new TagsRequest('12345');

    expect($request->resolveEndpoint())->toBe('/workspaces/12345/tags');
});

it('uses GET method', function () {
    $request = new TagsRequest('12345');

    expect($request->getMethod()->value)->toBe('GET');
});
