<?php

namespace Mort\Automation;

use Illuminate\Support\ServiceProvider;
use Mort\Automation\Commands\DevelopmentAutomationCommand;
use Mort\Automation\Commands\HelpCommand;
use Mort\Automation\Commands\InitializeCommand;
use Mort\Automation\Commands\MCPAutomationCommand;
use Mort\Automation\Commands\ReleaseCommand;
use Mort\Automation\Commands\StripeMCPAutomationCommand;
use Mort\Automation\Commands\SystemMonitoringCommand;
use Mort\Automation\Commands\WorkflowAutomationCommand;

class AutomationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/automation.php',
            'mort-automation'
        );

        // Registrar StripeService como singleton
        $this->app->singleton(\Mort\Automation\Services\StripeService::class, function ($app) {
            return new \Mort\Automation\Services\StripeService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Config/automation.php' => config_path('mort-automation.php'),
            ], 'mort-automation-config');

            $this->publishes([
                __DIR__.'/Database/Migrations' => database_path('migrations'),
            ], 'mort-automation-migrations');

            $this->commands([
                InitializeCommand::class,
                HelpCommand::class,
                DevelopmentAutomationCommand::class,
                WorkflowAutomationCommand::class,
                MCPAutomationCommand::class,
                StripeMCPAutomationCommand::class,
                \Mort\Automation\Commands\MCPServerCommand::class,
                SystemMonitoringCommand::class,
                ReleaseCommand::class,
            ]);
        }
    }
}
