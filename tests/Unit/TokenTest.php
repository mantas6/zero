<?php

use App\Token;

it('stores token with fillable name', function () {
    $token = Token::factory()->create(['name' => 'default']);

    expect($token->name)->toBe('default');
});

it('stores contents and workspace id', function () {
    $token = Token::factory()->create([
        'contents' => 'test-api-token',
        'default_workspace_id' => '7654321',
    ]);

    expect($token->contents)->toBe('test-api-token')
        ->and($token->default_workspace_id)->toBe('7654321');
});
