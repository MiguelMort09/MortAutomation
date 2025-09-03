l <?php

namespace Mort\Automation\Commands;

use Illuminate\Console\Command;
use Mort\Automation\Traits\ExecutesCommands;

class InitializeCommand extends Command
{
    use ExecutesCommands;

    protected $signature = 'mort:init 
                            {--force : Forzar la instalación sin confirmación}
                            {--dev : Instalar dependencias de desarrollo}';

    protected $description = 'Inicializa el package Mort Automation instalando todas las dependencias necesarias';

    public function handle(): int
    {
        $this->info('🚀 Iniciando configuración de Mort Automation...');
        $this->newLine();

        // Verificar si ya está instalado
        if (!$this->option('force') && $this->isAlreadyInstalled()) {
            $this->warn('⚠️  Mort Automation ya parece estar instalado.');
            if (!$this->confirm('¿Deseas continuar de todos modos?')) {
                $this->info('Operación cancelada.');
                return self::SUCCESS;
            }
        }

        try {
            // 1. Instalar dependencias de Composer
            $this->installComposerDependencies();

            // 2. Instalar dependencias de NPM
            $this->installNpmDependencies();

            // 3. Configurar archivos de configuración
            $this->setupConfigurationFiles();

            // 4. Ejecutar migraciones si es necesario
            $this->runMigrations();

            // 5. Publicar assets
            $this->publishAssets();

            // 6. Verificar instalación
            $this->verifyInstallation();

            $this->newLine();
            $this->info('✅ ¡Mort Automation se ha instalado correctamente!');
            $this->info('📚 Ejecuta "php artisan mort:help" para ver todos los comandos disponibles.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error durante la instalación: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function isAlreadyInstalled(): bool
    {
        return file_exists(base_path('vendor/mort/automation')) ||
               file_exists(base_path('packages/mort/automation'));
    }

    private function installComposerDependencies(): void
    {
        $this->info('📦 Instalando dependencias de Composer...');
        
        $command = 'composer install --no-interaction --prefer-dist --optimize-autoloader';
        
        if ($this->option('dev')) {
            $command .= ' --dev';
        } else {
            $command .= ' --no-dev';
        }

        $this->executeCommand($command);
        $this->info('✅ Dependencias de Composer instaladas');
    }

    private function installNpmDependencies(): void
    {
        $this->info('📦 Instalando dependencias de NPM...');
        
        if (!$this->commandExists('npm')) {
            $this->warn('⚠️  NPM no está disponible, saltando instalación de dependencias de Node.js');
            return;
        }

        $this->executeCommand('npm install');
        $this->info('✅ Dependencias de NPM instaladas');
    }

    private function setupConfigurationFiles(): void
    {
        $this->info('⚙️  Configurando archivos de configuración...');

        // Crear archivo de configuración si no existe
        $configPath = config_path('automation.php');
        if (!file_exists($configPath)) {
            $this->executeCommand('php artisan vendor:publish --provider="Mort\\Automation\\AutomationServiceProvider" --tag="config"');
            $this->info('✅ Archivo de configuración creado');
        } else {
            $this->info('ℹ️  Archivo de configuración ya existe');
        }
    }

    private function runMigrations(): void
    {
        $this->info('🗄️  Ejecutando migraciones...');
        
        try {
            $this->executeCommand('php artisan migrate --force');
            $this->info('✅ Migraciones ejecutadas');
        } catch (\Exception $e) {
            $this->warn('⚠️  No se pudieron ejecutar las migraciones: ' . $e->getMessage());
        }
    }

    private function publishAssets(): void
    {
        $this->info('📁 Publicando assets...');
        
        try {
            $this->executeCommand('php artisan vendor:publish --provider="Mort\\Automation\\AutomationServiceProvider" --tag="assets" --force');
            $this->info('✅ Assets publicados');
        } catch (\Exception $e) {
            $this->warn('⚠️  No se pudieron publicar los assets: ' . $e->getMessage());
        }
    }

    private function verifyInstallation(): void
    {
        $this->info('🔍 Verificando instalación...');

        $checks = [
            'Composer autoload' => function() {
                return class_exists('Mort\Automation\AutomationServiceProvider');
            },
            'Comandos disponibles' => function() {
                $output = shell_exec('php artisan list | grep mort:');
                return !empty($output);
            },
            'Configuración' => function() {
                return file_exists(config_path('automation.php'));
            }
        ];

        foreach ($checks as $check => $callback) {
            if ($callback()) {
                $this->info("  ✅ {$check}");
            } else {
                $this->error("  ❌ {$check}");
            }
        }
    }
}
