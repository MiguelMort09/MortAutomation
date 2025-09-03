<?php

use Mort\Automation\AutomationServiceProvider;

beforeEach(function () {
    $this->app->register(AutomationServiceProvider::class);
});

it('can register the automation service provider', function () {
    expect($this->app->getProviders())
        ->toContain(AutomationServiceProvider::class);
});

it('can access automation configuration', function () {
    $config = config('automation');
    
    expect($config)
        ->toBeArray()
        ->and($config['version'])
        ->toBeString();
});
