<?php

use App\Http\Integrations\Toggl\Requests\TasksRequest;

it('resolves the correct endpoint', function () {
    $request = new TasksRequest('12345', 67890);

    expect($request->resolveEndpoint())
        ->toBe('/workspaces/12345/projects/67890/tasks');
});

it('uses GET method', function () {
    $request = new TasksRequest('12345', 67890);

    expect($request->getMethod()->value)->toBe('GET');
});

it('includes active=true query when filtering active', function () {
    $request = new TasksRequest('12345', 67890, active: true);

    expect($request->query()->all())->toBe(['active' => 'true']);
});

it('includes active=false query when filtering inactive', function () {
    $request = new TasksRequest('12345', 67890, active: false);

    expect($request->query()->all())->toBe(['active' => 'false']);
});

it('omits active query when null', function () {
    $request = new TasksRequest('12345', 67890, active: null);

    expect($request->query()->all())->toBe([]);
});
