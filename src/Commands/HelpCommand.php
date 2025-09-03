<?php

namespace Mort\Automation\Commands;

use Illuminate\Console\Command;

class HelpCommand extends Command
{
    protected $signature = 'mort:help 
                            {cmd? : Comando específico para mostrar ayuda detallada}';

    protected $description = 'Muestra ayuda y documentación para los comandos de Mort Automation';

    public function handle(): int
    {
        $command = $this->argument('cmd');

        if ($command) {
            return $this->showCommandHelp($command);
        }

        $this->showGeneralHelp();
        return self::SUCCESS;
    }

    private function showGeneralHelp(): void
    {
        $this->info('🚀 Mort Automation - Herramientas de Desarrollo y Monitoreo');
        $this->newLine();
        
        $this->info('📋 Comandos Disponibles:');
        $this->newLine();

        $commands = [
            'mort:init' => [
                'description' => 'Inicializa completamente el package: instala dependencias, configura archivos y prepara el entorno',
                'options' => ['--force', '--dev'],
                'example' => 'php artisan mort:init --force'
            ],
            'mort:dev setup' => [
                'description' => 'Configura el entorno de desarrollo: instala dependencias, configura archivos y prepara herramientas',
                'options' => ['--force', '--skip-tests'],
                'example' => 'php artisan mort:dev setup'
            ],
            'mort:dev test' => [
                'description' => 'Ejecuta todos los tests con Pest y genera reportes de cobertura de código',
                'options' => ['--coverage', '--filter'],
                'example' => 'php artisan mort:dev test --coverage'
            ],
            'mort:workflow start-feature' => [
                'description' => 'Inicia nueva feature: crea rama, configura entorno y prepara para desarrollo',
                'options' => ['--name', '--template'],
                'example' => 'php artisan mort:workflow start-feature --name="mi-feature"'
            ],
            'mort:mcp setup' => [
                'description' => 'Configura todos los MCPs: Laravel Boost, Context7, GitHub y Stripe',
                'options' => ['--laravel-boost', '--context7', '--github', '--stripe'],
                'example' => 'php artisan mort:mcp setup --all'
            ],
            'mort:stripe sync' => [
                'description' => 'Sincroniza productos y precios entre la base de datos local y Stripe',
                'options' => ['--force', '--dry-run'],
                'example' => 'php artisan mort:stripe sync --force'
            ],
            'mort:monitor' => [
                'description' => 'Verifica estado del sistema: BD, performance, logs y métricas generales',
                'options' => ['--detailed', '--export'],
                'example' => 'php artisan mort:monitor --detailed'
            ],
        ];

        foreach ($commands as $command => $info) {
            $this->line("  <fg=green>{$command}</>");
            $this->line("    {$info['description']}");
            if (!empty($info['options'])) {
                $this->line("    <fg=yellow>Opciones:</> " . implode(', ', $info['options']));
            }
            $this->line("    <fg=blue>Ejemplo:</> {$info['example']}");
            $this->newLine();
        }

        $this->info('📚 Para más información sobre un comando específico:');
        $this->line('  php artisan mort:help <comando>');
        $this->newLine();

        $this->info('🔗 Recursos Adicionales:');
        $this->line('  - Documentación: https://github.com/MiguelMort09/MortAutomation');
        $this->line('  - Issues: https://github.com/MiguelMort09/MortAutomation/issues');
        $this->line('  - Versión actual: 1.2.2');
    }

    private function showCommandHelp(string $command): int
    {
        $helpData = [
            'init' => [
                'title' => 'Comando de Inicialización',
                'description' => 'Inicializa completamente el package Mort Automation instalando todas las dependencias necesarias y configurando el entorno.',
                'usage' => 'php artisan mort:init [opciones]',
                'options' => [
                    '--force' => 'Forzar la instalación sin confirmación',
                    '--dev' => 'Instalar dependencias de desarrollo'
                ],
                'examples' => [
                    'php artisan mort:init',
                    'php artisan mort:init --force',
                    'php artisan mort:init --dev'
                ],
                'steps' => [
                    'Instala dependencias de Composer',
                    'Instala dependencias de NPM',
                    'Configura archivos de configuración',
                    'Ejecuta migraciones',
                    'Publica assets',
                    'Verifica la instalación'
                ]
            ],
            'dev' => [
                'title' => 'Comandos de Desarrollo',
                'description' => 'Conjunto de comandos para automatizar tareas de desarrollo.',
                'subcommands' => [
                    'setup' => 'Configura el entorno de desarrollo',
                    'test' => 'Ejecuta tests con configuración optimizada',
                    'lint' => 'Ejecuta herramientas de análisis de código',
                    'format' => 'Formatea el código según los estándares'
                ]
            ],
            'monitor' => [
                'title' => 'Sistema de Monitoreo',
                'description' => 'Monitorea el estado del sistema, base de datos, seguridad y rendimiento.',
                'usage' => 'php artisan mort:monitor [opciones]',
                'options' => [
                    '--detailed' => 'Mostrar información detallada',
                    '--export' => 'Exportar resultados a archivo'
                ],
                'checks' => [
                    'Estado del servidor web',
                    'Conectividad de base de datos',
                    'Métricas de seguridad',
                    'Rendimiento del sistema',
                    'Espacio en disco',
                    'Logs de errores'
                ]
            ]
        ];

        if (!isset($helpData[$command])) {
            $this->error("❌ Comando '{$command}' no encontrado.");
            $this->info('Comandos disponibles: ' . implode(', ', array_keys($helpData)));
            return self::FAILURE;
        }

        $data = $helpData[$command];
        
        $this->info("📖 {$data['title']}");
        $this->newLine();
        $this->line($data['description']);
        $this->newLine();

        if (isset($data['usage'])) {
            $this->info('💻 Uso:');
            $this->line("  {$data['usage']}");
            $this->newLine();
        }

        if (isset($data['options'])) {
            $this->info('⚙️  Opciones:');
            foreach ($data['options'] as $option => $description) {
                $this->line("  <fg=yellow>{$option}</> - {$description}");
            }
            $this->newLine();
        }

        if (isset($data['examples'])) {
            $this->info('📝 Ejemplos:');
            foreach ($data['examples'] as $example) {
                $this->line("  {$example}");
            }
            $this->newLine();
        }

        if (isset($data['steps'])) {
            $this->info('🔄 Pasos que ejecuta:');
            foreach ($data['steps'] as $step) {
                $this->line("  • {$step}");
            }
            $this->newLine();
        }

        if (isset($data['checks'])) {
            $this->info('🔍 Verificaciones que realiza:');
            foreach ($data['checks'] as $check) {
                $this->line("  • {$check}");
            }
            $this->newLine();
        }

        if (isset($data['subcommands'])) {
            $this->info('📋 Subcomandos:');
            foreach ($data['subcommands'] as $subcommand => $description) {
                $this->line("  <fg=green>mort:{$command} {$subcommand}</> - {$description}");
            }
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
