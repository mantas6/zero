<?php

use App\Http\Integrations\Toggl\Requests\ProjectsRequest;

it('resolves the correct endpoint', function () {
    $request = new ProjectsRequest('12345');

    expect($request->resolveEndpoint())->toBe('/workspaces/12345/projects');
});

it('uses GET method', function () {
    $request = new ProjectsRequest('12345');

    expect($request->getMethod()->value)->toBe('GET');
});
