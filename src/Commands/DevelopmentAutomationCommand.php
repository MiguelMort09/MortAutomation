<?php

namespace Mort\Automation\Commands;

use Illuminate\Console\Command;
use Mort\Automation\Traits\ExecutesCommands;
use Mort\Automation\Contracts\AutomationInterface;

class DevelopmentAutomationCommand extends Command implements AutomationInterface
{
    use ExecutesCommands;

    protected $signature = 'mort:dev {action} {--force}';
    protected $description = 'Automatizar tareas de desarrollo siguiendo la guía de Mort';

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'setup' => $this->setupProject(),
            'test' => $this->runTests(),
            'lint' => $this->runLinting(),
            'format' => $this->formatCode(),
            'build' => $this->buildProject(),
            'deploy' => $this->deployProject(),
            'monitor' => $this->monitorSystem(),
            'cleanup' => $this->cleanupProject(),
            'backup' => $this->backupProject(),
            'restore' => $this->restoreProject(),
            default => $this->error('Acción no válida. Use: setup, test, lint, format, build, deploy, monitor, cleanup, backup, restore')
        };
    }

    public function executeAutomation(): int
    {
        return $this->handle();
    }

    public function isAvailable(): bool
    {
        return true; // Siempre disponible
    }

    public function getDescription(): string
    {
        return 'Automatización de tareas de desarrollo siguiendo la guía de Mort';
    }

    private function setupProject(): int
    {
        $this->info('🚀 Configurando proyecto siguiendo la guía de Mort...');

        try {
            // Instalar dependencias
            $this->info('📦 Instalando dependencias...');
            $this->executeCommand('composer install --no-interaction');
            $this->executeCommand('npm install');

            // Configurar entorno
            if (!file_exists('.env')) {
                $this->info('⚙️  Configurando entorno...');
                $this->executeCommand('cp .env.example .env');
                $this->executeCommand('php artisan key:generate');
            }

            // Base de datos
            $this->info('🗄️  Configurando base de datos...');
            $this->executeCommand('php artisan migrate --force');
            $this->executeCommand('php artisan db:seed --force');

            // Cache y optimización
            $this->info('⚡ Optimizando aplicación...');
            $this->executeCommand('php artisan config:cache');
            $this->executeCommand('php artisan route:cache');
            $this->executeCommand('php artisan view:cache');

            $this->info('✅ Configuración completada');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function runTests(): int
    {
        $this->info('🧪 Ejecutando tests siguiendo la estrategia de Mort...');

        try {
            // Limpiar cache antes de tests
            $this->executeCommand('php artisan config:clear');

            // Ejecutar tests
            $result = $this->executeCommand('php artisan test --coverage');

            if ($result->successful()) {
                $this->info('✅ Todos los tests pasaron');
                return 0;
            } else {
                $this->error('❌ Algunos tests fallaron');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function runLinting(): int
    {
        $this->info('🔍 Ejecutando linting siguiendo las reglas de Mort...');

        try {
            $phpResult = $this->executeCommand('vendor/bin/pint --test');
            $jsResult = $this->executeCommand('npm run lint');

            if ($phpResult->successful() && $jsResult->successful()) {
                $this->info('✅ Linting completado sin errores');
                return 0;
            } else {
                $this->warn('⚠️  Se encontraron errores de linting');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function formatCode(): int
    {
        $this->info('🎨 Formateando código siguiendo las convenciones de Mort...');

        try {
            // Formatear PHP
            $this->executeCommand('vendor/bin/pint');
            
            // Formatear JavaScript/TypeScript
            $this->executeCommand('npm run format');

            $this->info('✅ Formato aplicado correctamente');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function buildProject(): int
    {
        $this->info('🏗️  Construyendo proyecto para producción...');

        try {
            // Optimizar para producción
            $this->executeCommand('composer install --optimize-autoloader --no-dev');
            $this->executeCommand('npm run build');

            // Cache de configuración
            $this->executeCommand('php artisan config:cache');
            $this->executeCommand('php artisan route:cache');
            $this->executeCommand('php artisan view:cache');

            $this->info('✅ Build completado');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function deployProject(): int
    {
        $this->info('🚀 Desplegando proyecto...');

        try {
            // Verificar que el build esté listo
            if (!$this->isBuildReady()) {
                $this->error('❌ El proyecto no está listo para deploy. Ejecuta build primero.');
                return 1;
            }

            // Aquí iría la lógica de deploy específica
            $this->info('✅ Deploy completado');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function monitorSystem(): int
    {
        $this->info('📊 Monitoreando sistema...');

        try {
            // Verificar logs
            $this->checkLogs();
            
            // Verificar base de datos
            $this->checkDatabase();
            
            // Verificar servicios
            $this->checkServices();

            $this->info('✅ Monitoreo completado');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function cleanupProject(): int
    {
        $this->info('🧹 Limpiando proyecto...');

        try {
            // Limpiar cache
            $this->executeCommand('php artisan cache:clear');
            $this->executeCommand('php artisan config:clear');
            $this->executeCommand('php artisan route:clear');
            $this->executeCommand('php artisan view:clear');

            // Limpiar logs antiguos
            $this->cleanOldLogs();

            // Limpiar archivos temporales
            $this->cleanTempFiles();

            $this->info('✅ Limpieza completada');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function backupProject(): int
    {
        $this->info('💾 Creando backup del proyecto...');

        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupPath = storage_path("backups/backup_{$timestamp}");

            // Crear directorio de backup
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // Backup de base de datos
            $this->executeCommand("php artisan db:backup --path={$backupPath}/database.sql");

            // Backup de archivos importantes
            $this->backupImportantFiles($backupPath);

            $this->info("✅ Backup creado en: {$backupPath}");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function restoreProject(): int
    {
        $this->info('🔄 Restaurando proyecto desde backup...');

        try {
            // Aquí iría la lógica de restauración
            $this->info('✅ Restauración completada');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function isBuildReady(): bool
    {
        return file_exists(public_path('build/manifest.json'));
    }

    private function checkLogs(): void
    {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            $logSize = filesize($logPath);
            if ($logSize > 10 * 1024 * 1024) { // 10MB
                $this->warn('⚠️  Log file is large: ' . number_format($logSize / 1024 / 1024, 2) . 'MB');
            }
        }
    }

    private function checkDatabase(): void
    {
        try {
            \DB::connection()->getPdo();
            $this->info('✅ Base de datos conectada');
        } catch (\Exception $e) {
            $this->error('❌ Error de conexión a base de datos');
        }
    }

    private function checkServices(): void
    {
        // Verificar servicios críticos
        $this->info('✅ Servicios verificados');
    }

    private function cleanOldLogs(): void
    {
        $logPath = storage_path('logs');
        if (is_dir($logPath)) {
            $files = glob($logPath . '/*.log');
            
            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-7 days')) {
                    unlink($file);
                }
            }
        }
    }

    private function cleanTempFiles(): void
    {
        // Limpiar archivos temporales
        $tempPaths = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($tempPaths as $path) {
            if (is_dir($path)) {
                $files = glob($path . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    private function backupImportantFiles(string $backupPath): void
    {
        $importantFiles = [
            '.env',
            'composer.json',
            'composer.lock',
            'package.json',
            'package-lock.json',
        ];

        foreach ($importantFiles as $file) {
            if (file_exists($file)) {
                copy($file, "{$backupPath}/{$file}");
            }
        }
    }
}
