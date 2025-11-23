<?php

namespace Mort\Automation\Commands;

use Illuminate\Console\Command;
use Mort\Automation\Contracts\AutomationInterface;
use Mort\Automation\Traits\ExecutesCommands;

class WorkflowAutomationCommand extends Command implements AutomationInterface
{
    use ExecutesCommands;

    protected $signature = 'mort:workflow {action} {--name=} {--branch=} {--force}';

    protected $description = 'Automatizar workflow completo siguiendo la guía de Mort';

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'start-feature' => $this->startFeature(),
            'complete-feature' => $this->completeFeature(),
            'deploy-staging' => $this->deployStaging(),
            'deploy-production' => $this->deployProduction(),
            'full-cycle' => $this->fullCycle(),
            'emergency-fix' => $this->emergencyFix(),
            default => $this->error('Acción no válida. Use: start-feature, complete-feature, deploy-staging, deploy-production, full-cycle, emergency-fix')
        };
    }

    public function executeAutomation(): int
    {
        return $this->handle();
    }

    public function isAvailable(): bool
    {
        return $this->commandExists('git');
    }

    public function getDescription(): string
    {
        return 'Automatización de workflow completo siguiendo la guía de Mort';
    }

    private function startFeature(): int
    {
        $featureName = $this->option('name');

        if (! $featureName) {
            $featureName = $this->ask('¿Nombre de la feature?');
        }

        if (! $featureName) {
            $this->error('❌ Se requiere un nombre para la feature');

            return 1;
        }

        $this->info("🚀 Iniciando feature: {$featureName}");

        try {
            // Verificar que estamos en la rama principal
            $currentBranch = $this->getCurrentBranch();
            if ($currentBranch !== 'main' && $currentBranch !== 'master') {
                $this->warn("⚠️  No estás en la rama principal (actual: {$currentBranch})");
                if (! $this->confirm('¿Continuar?')) {
                    return 1;
                }
            }

            // Crear nueva rama
            $branchName = $this->option('branch') ?? "feature/{$featureName}";
            $this->info("📝 Creando rama: {$branchName}");

            $this->executeCommand("git checkout -b {$branchName}");

            // Configurar entorno para desarrollo
            $this->info('⚙️  Configurando entorno de desarrollo...');
            $this->executeCommand('composer install');

            if ($this->commandExists('npm')) {
                $this->executeCommand('npm install');
            }

            // Limpiar cache
            $this->executeCommand('php artisan config:clear');
            $this->executeCommand('php artisan cache:clear');

            $this->info("✅ Feature '{$featureName}' iniciada en rama '{$branchName}'");
            $this->line('');
            $this->info('📋 Próximos pasos:');
            $this->line('  1. Desarrollar la feature');
            $this->line('  2. Ejecutar: php artisan mort:workflow complete-feature');
            $this->line('  3. Crear Pull Request');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function completeFeature(): int
    {
        $this->info('🏁 Completando feature...');

        try {
            $currentBranch = $this->getCurrentBranch();

            if (! str_starts_with($currentBranch, 'feature/')) {
                $this->error('❌ No estás en una rama de feature');

                return 1;
            }

            // Ejecutar tests
            $this->info('🧪 Ejecutando tests...');
            $testResult = $this->executeCommand('php artisan test');

            if (! $testResult->successful()) {
                $this->error('❌ Los tests fallaron. Corrige los errores antes de continuar.');

                return 1;
            }

            // Ejecutar linting
            $this->info('🔍 Ejecutando linting...');
            $lintResult = $this->executeCommand('vendor/bin/pint --test');

            if (! $lintResult->successful()) {
                $this->warn('⚠️  Se encontraron errores de linting');
                if ($this->confirm('¿Aplicar formato automáticamente?')) {
                    $this->executeCommand('vendor/bin/pint');
                }
            }

            // Formatear código
            $this->info('🎨 Formateando código...');
            $this->executeCommand('vendor/bin/pint');

            if ($this->commandExists('npm')) {
                $this->executeCommand('npm run format');
            }

            // Commit de cambios
            if ($this->confirm('¿Hacer commit de los cambios?')) {
                $commitMessage = $this->ask('Mensaje de commit (opcional)') ?? 'feat: completar feature';
                $this->executeCommand('git add .');
                $this->executeCommand("git commit -m '{$commitMessage}'");
            }

            $this->info("✅ Feature completada en rama '{$currentBranch}'");
            $this->line('');
            $this->info('📋 Próximos pasos:');
            $this->line('  1. Push de la rama: git push origin '.$currentBranch);
            $this->line('  2. Crear Pull Request');
            $this->line('  3. Ejecutar: php artisan mort:workflow deploy-staging');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function deployStaging(): int
    {
        $this->info('🚀 Desplegando a staging...');

        try {
            // Verificar que estamos en la rama correcta
            $currentBranch = $this->getCurrentBranch();

            if ($currentBranch !== 'staging') {
                $this->warn("⚠️  No estás en la rama staging (actual: {$currentBranch})");
                if (! $this->confirm('¿Continuar?')) {
                    return 1;
                }
            }

            // Build para staging
            $this->info('🏗️  Construyendo para staging...');
            $this->executeCommand('composer install --optimize-autoloader');

            if ($this->commandExists('npm')) {
                $this->executeCommand('npm run build');
            }

            // Cache de configuración
            $this->executeCommand('php artisan config:cache');
            $this->executeCommand('php artisan route:cache');
            $this->executeCommand('php artisan view:cache');

            // Migraciones
            $this->info('🗄️  Ejecutando migraciones...');
            $this->executeCommand('php artisan migrate --force');

            // Tests en staging
            $this->info('🧪 Ejecutando tests en staging...');
            $testResult = $this->executeCommand('php artisan test');

            if (! $testResult->successful()) {
                $this->error('❌ Los tests fallaron en staging');

                return 1;
            }

            $this->info('✅ Deploy a staging completado');
            $this->line('');
            $this->info('📋 Próximos pasos:');
            $this->line('  1. Verificar funcionamiento en staging');
            $this->line('  2. Ejecutar: php artisan mort:workflow deploy-production');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function deployProduction(): int
    {
        $this->info('🚀 Desplegando a producción...');

        try {
            // Verificar que estamos en la rama correcta
            $currentBranch = $this->getCurrentBranch();

            if ($currentBranch !== 'main' && $currentBranch !== 'master') {
                $this->error('❌ Debes estar en la rama principal para deploy a producción');

                return 1;
            }

            // Confirmación final
            if (! $this->confirm('¿Estás seguro de desplegar a producción?')) {
                $this->info('Deploy cancelado');

                return 0;
            }

            // Build para producción
            $this->info('🏗️  Construyendo para producción...');
            $this->executeCommand('composer install --optimize-autoloader --no-dev');

            if ($this->commandExists('npm')) {
                $this->executeCommand('npm run build');
            }

            // Cache de configuración
            $this->executeCommand('php artisan config:cache');
            $this->executeCommand('php artisan route:cache');
            $this->executeCommand('php artisan view:cache');

            // Migraciones
            $this->info('🗄️  Ejecutando migraciones...');
            $this->executeCommand('php artisan migrate --force');

            // Limpiar cache
            $this->executeCommand('php artisan cache:clear');

            $this->info('✅ Deploy a producción completado');
            $this->line('');
            $this->info('🎉 ¡La aplicación está en producción!');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function fullCycle(): int
    {
        $this->info('🔄 Ejecutando ciclo completo de desarrollo...');

        try {
            // 1. Iniciar feature
            $featureName = $this->ask('¿Nombre de la feature?');
            if (! $featureName) {
                $this->error('❌ Se requiere un nombre para la feature');

                return 1;
            }

            $this->info("🚀 Paso 1: Iniciando feature '{$featureName}'");
            $this->executeCommand("php artisan mort:workflow start-feature --name={$featureName}");

            // 2. Completar feature
            $this->info('🏁 Paso 2: Completando feature');
            $this->executeCommand('php artisan mort:workflow complete-feature');

            // 3. Deploy a staging
            $this->info('🚀 Paso 3: Deploy a staging');
            $this->executeCommand('php artisan mort:workflow deploy-staging');

            // 4. Deploy a producción (opcional)
            if ($this->confirm('¿Deploy a producción?')) {
                $this->info('🚀 Paso 4: Deploy a producción');
                $this->executeCommand('php artisan mort:workflow deploy-production');
            }

            $this->info('✅ Ciclo completo ejecutado');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function emergencyFix(): int
    {
        $this->info('🚨 Iniciando fix de emergencia...');

        try {
            // Crear rama de hotfix
            $hotfixName = $this->ask('¿Nombre del hotfix?');
            if (! $hotfixName) {
                $this->error('❌ Se requiere un nombre para el hotfix');

                return 1;
            }

            $branchName = "hotfix/{$hotfixName}";
            $this->info("📝 Creando rama de hotfix: {$branchName}");

            $this->executeCommand("git checkout -b {$branchName}");

            $this->info("✅ Hotfix '{$hotfixName}' iniciado en rama '{$branchName}'");
            $this->line('');
            $this->info('📋 Próximos pasos:');
            $this->line('  1. Implementar el fix');
            $this->line('  2. Ejecutar tests');
            $this->line('  3. Commit y push');
            $this->line('  4. Merge rápido a producción');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function getCurrentBranch(): string
    {
        $result = $this->executeCommand('git branch --show-current');

        return trim($result->output());
    }
}
