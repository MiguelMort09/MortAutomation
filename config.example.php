<?php

return [
    'commands' => [
        'dev' => \Mort\Automation\Commands\DevelopmentAutomationCommand::class,
        'mcp' => \Mort\Automation\Commands\MCPAutomationCommand::class,
        'stripe' => \Mort\Automation\Commands\StripeMCPAutomationCommand::class,
        'monitor' => \Mort\Automation\Commands\SystemMonitoringCommand::class,
        'workflow' => \Mort\Automation\Commands\WorkflowAutomationCommand::class,
    ],
    'mcp' => [
        'github_token' => env('GITHUB_TOKEN'),
        'stripe_key' => config('cashier.key'),
        'stripe_secret' => config('cashier.secret'),
    ],
    'stripe' => [
        'enabled' => env('STRIPE_AUTOMATION_ENABLED', true),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'sync_interval' => env('STRIPE_SYNC_INTERVAL', 3600), // 1 hora
    ],
    'github' => [
        'enabled' => env('GITHUB_AUTOMATION_ENABLED', true),
        'default_branch' => env('GITHUB_DEFAULT_BRANCH', 'main'),
        'auto_merge' => env('GITHUB_AUTO_MERGE', false),
    ],
    'monitoring' => [
        'enabled' => env('MONITORING_ENABLED', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // ms
        'memory_limit_warning' => env('MEMORY_LIMIT_WARNING', 80), // %
        'export_path' => storage_path('monitoring'),
    ],
    'workflow' => [
        'auto_commit' => env('WORKFLOW_AUTO_COMMIT', false),
        'feature_branch_prefix' => env('FEATURE_BRANCH_PREFIX', 'feature'),
        'hotfix_branch_prefix' => env('HOTFIX_BRANCH_PREFIX', 'hotfix'),
        'staging_branch' => env('STAGING_BRANCH', 'staging'),
        'production_branch' => env('PRODUCTION_BRANCH', 'main'),
    ],
    'development' => [
        'auto_install_deps' => env('AUTO_INSTALL_DEPS', true),
        'run_tests_after_setup' => env('RUN_TESTS_AFTER_SETUP', true),
        'format_code_after_setup' => env('FORMAT_CODE_AFTER_SETUP', true),
    ],
];
