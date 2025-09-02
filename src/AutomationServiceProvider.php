<?php

namespace Mort\Automation;

use Illuminate\Support\ServiceProvider;
use Mort\Automation\Commands\DevelopmentAutomationCommand;
use Mort\Automation\Commands\WorkflowAutomationCommand;
use Mort\Automation\Commands\MCPAutomationCommand;
use Mort\Automation\Commands\StripeMCPAutomationCommand;
use Mort\Automation\Commands\SystemMonitoringCommand;

class AutomationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/automation.php', 'automation'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Config/automation.php' => config_path('automation.php'),
            ], 'config');

            $this->commands([
                DevelopmentAutomationCommand::class,
                WorkflowAutomationCommand::class,
                MCPAutomationCommand::class,
                StripeMCPAutomationCommand::class,
                SystemMonitoringCommand::class,
            ]);
        }
    }
}
