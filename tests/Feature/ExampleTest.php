<?php

use Mort\Automation\AutomationServiceProvider;

beforeEach(function () {
    $this->app->register(AutomationServiceProvider::class);
});

it('can register the automation service provider', function () {
    $providers = $this->app->getProviders(AutomationServiceProvider::class);
    
    expect($providers)
        ->not->toBeEmpty();
});

it('can access automation configuration', function () {
    // Cargar la configuración del package
    $configPath = __DIR__ . '/../../src/Config/automation.php';
    $config = require $configPath;
    
    expect($config)
        ->toBeArray()
        ->and($config['version'])
        ->toBeString();
});
