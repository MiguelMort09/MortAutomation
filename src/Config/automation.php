<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mort Automation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para el package Mort Automation que incluye
    | herramientas de desarrollo, monitoreo y automatización.
    |
    */

    'version' => '1.5.0',

    /*
    |--------------------------------------------------------------------------
    | Configuración de Comandos
    |--------------------------------------------------------------------------
    |
    | Configuración específica para cada tipo de comando de automatización.
    |
    */

    'commands' => [
        'development' => [
            'enabled' => true,
            'auto_install_dependencies' => true,
            'run_tests_after_changes' => true,
        ],

        'monitoring' => [
            'enabled' => true,
            'check_interval' => 300, // segundos
            'alert_thresholds' => [
                'cpu_usage' => 80,
                'memory_usage' => 85,
                'disk_usage' => 90,
            ],
        ],

        'workflow' => [
            'enabled' => true,
            'auto_commit' => false,
            'commit_message_template' => 'feat: {description}',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de MCPs
    |--------------------------------------------------------------------------
    |
    | Configuración para los Model Context Protocols (MCPs) integrados.
    |
    */

    'mcps' => [
        'laravel_boost' => [
            'enabled' => true,
            'auto_configure' => true,
        ],

        'context7' => [
            'enabled' => true,
            'auto_configure' => true,
        ],

        'github' => [
            'enabled' => true,
            'auto_configure' => true,
        ],

        'stripe' => [
            'enabled' => true,
            'auto_configure' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Logging
    |--------------------------------------------------------------------------
    |
    | Configuración para el sistema de logging del package.
    |
    */

    'logging' => [
        'enabled' => true,
        'level' => 'info',
        'channels' => [
            'automation' => 'daily',
            'monitoring' => 'daily',
            'errors' => 'daily',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Notificaciones
    |--------------------------------------------------------------------------
    |
    | Configuración para notificaciones del sistema de monitoreo.
    |
    */

    'notifications' => [
        'enabled' => true,
        'channels' => [
            'mail' => [
                'enabled' => true,
                'recipients' => [],
            ],
            'slack' => [
                'enabled' => false,
                'webhook_url' => null,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Cache
    |--------------------------------------------------------------------------
    |
    | Configuración para el sistema de cache del package.
    |
    */

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // segundos
        'prefix' => 'mort_automation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad
    |--------------------------------------------------------------------------
    |
    | Configuración de seguridad para el package.
    |
    */

    'security' => [
        'encrypt_sensitive_data' => true,
        'log_security_events' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_attempts' => 10,
            'decay_minutes' => 1,
        ],
    ],
];
