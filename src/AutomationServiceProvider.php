<?php

namespace Mort\Automation;

use Illuminate\Support\ServiceProvider;
use Mort\Automation\Commands\DevelopmentAutomationCommand;
use Mort\Automation\Commands\WorkflowAutomationCommand;
use Mort\Automation\Commands\MCPAutomationCommand;
use Mort\Automation\Commands\StripeMCPAutomationCommand;
use Mort\Automation\Commands\SystemMonitoringCommand;
use Mort\Automation\Commands\InitializeCommand;
use Mort\Automation\Commands\HelpCommand;

class AutomationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/automation.php', 'mort-automation'
        );
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

            $this->commands([
                InitializeCommand::class,
                HelpCommand::class,
                DevelopmentAutomationCommand::class,
                WorkflowAutomationCommand::class,
                MCPAutomationCommand::class,
                StripeMCPAutomationCommand::class,
                SystemMonitoringCommand::class,
            ]);
        }
    }
}
