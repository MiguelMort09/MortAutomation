<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Automatización Mort
    |--------------------------------------------------------------------------
    |
    | Configuración para el package de automatización siguiendo la guía
    | de desarrollo de Mort.
    |
    */

    'stripe' => [
        'enabled' => env('STRIPE_AUTOMATION_ENABLED', true),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'github' => [
        'enabled' => env('GITHUB_AUTOMATION_ENABLED', true),
        'token' => env('GITHUB_TOKEN'),
        'default_branch' => env('GITHUB_DEFAULT_BRANCH', 'main'),
    ],

    'monitoring' => [
        'enabled' => env('MONITORING_ENABLED', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // ms
        'memory_limit_warning' => env('MEMORY_LIMIT_WARNING', 80), // %
    ],

    'workflow' => [
        'auto_commit' => env('WORKFLOW_AUTO_COMMIT', false),
        'default_feature_branch_prefix' => env('FEATURE_BRANCH_PREFIX', 'feature'),
        'default_hotfix_branch_prefix' => env('HOTFIX_BRANCH_PREFIX', 'hotfix'),
    ],

    'development' => [
        'auto_install_dependencies' => env('AUTO_INSTALL_DEPS', true),
        'run_tests_after_setup' => env('RUN_TESTS_AFTER_SETUP', true),
        'format_code_after_setup' => env('FORMAT_CODE_AFTER_SETUP', true),
    ],
];
