<?php

namespace Mort\Automation\Commands;

use Illuminate\Console\Command;
use Mort\Automation\Contracts\AutomationInterface;
use Mort\Automation\Traits\ExecutesCommands;

class ReleaseCommand extends Command implements AutomationInterface
{
    use ExecutesCommands;

    protected $signature = 'mort:release {type? : Tipo de incremento (patch, minor, major)} {--force : Forzar ejecución sin confirmación}';

    protected $description = 'Automatizar el proceso de release (changelog, tag, push)';

    public function handle(): int
    {
        $this->info('🚀 Iniciando proceso de release...');

        // 1. Obtener versión actual
        $currentVersion = $this->getCurrentVersion();
        $this->info("📌 Versión actual: {$currentVersion}");

        // 2. Determinar tipo de incremento
        $type = $this->argument('type');
        if (! $type) {
            $type = $this->choice('¿Qué tipo de release es?', ['patch', 'minor', 'major'], 'patch');
        }

        // 3. Calcular nueva versión
        $newVersion = $this->calculateNewVersion($currentVersion, $type);
        $this->info("✨ Nueva versión será: {$newVersion}");

        if (! $this->option('force') && ! $this->confirm('¿Continuar con este release?')) {
            $this->info('Operación cancelada.');

            return self::SUCCESS;
        }

        try {
            // 4. Actualizar CHANGELOG.md
            $this->updateChangelog($newVersion);

            // 5. Commit de cambios
            $this->info('📝 Creando commit de release...');
            $this->executeCommand('git add CHANGELOG.md');
            $this->executeCommand("git commit -m \"chore: release {$newVersion}\"");

            // 6. Crear Tag
            $this->info("🏷️  Creando tag {$newVersion}...");
            $this->executeCommand("git tag -a {$newVersion} -m \"Release {$newVersion}\"");

            // 7. Push
            if ($this->option('force') || $this->confirm('¿Hacer push al repositorio remoto?')) {
                $this->info('⬆️  Subiendo cambios...');
                $this->executeCommand('git push origin master --tags'); // Asumiendo master, idealmente detectar rama
            }

            $this->info("✅ Release {$newVersion} completado exitosamente!");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error durante el release: {$e->getMessage()}");

            return self::FAILURE;
        }
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
        return 'Automatizar el proceso de release (changelog, tag, push)';
    }

    private function getCurrentVersion(): string
    {
        try {
            $result = $this->executeCommand('git describe --tags --abbrev=0');

            return trim($result->output());
        } catch (\Exception $e) {
            return 'v0.0.0';
        }
    }

    private function calculateNewVersion(string $currentVersion, string $type): string
    {
        $version = ltrim($currentVersion, 'v');
        $parts = explode('.', $version);

        // Asegurar que tenemos 3 partes
        while (count($parts) < 3) {
            $parts[] = '0';
        }

        $major = (int) $parts[0];
        $minor = (int) $parts[1];
        $patch = (int) $parts[2];

        match ($type) {
            'major' => $major++,
            'minor' => $minor++,
            'patch' => $patch++,
            default => $patch++
        };

        // Resetear partes menores
        if ($type === 'major') {
            $minor = 0;
            $patch = 0;
        } elseif ($type === 'minor') {
            $patch = 0;
        }

        return "v{$major}.{$minor}.{$patch}";
    }

    private function updateChangelog(string $version): void
    {
        $this->info('📄 Actualizando CHANGELOG.md...');

        $changelogPath = base_path('CHANGELOG.md');

        if (! file_exists($changelogPath)) {
            $this->warn('⚠️  CHANGELOG.md no encontrado, creando uno nuevo...');
            file_put_contents($changelogPath, "# Changelog\n\n");
        }

        $content = file_get_contents($changelogPath);
        $date = date('Y-m-d');

        $newEntry = "\n## [{$version}] - {$date}\n\n### Agregado\n- Nuevo release generado automáticamente.\n";

        // Insertar después del primer encabezado o al principio si no hay estructura clara
        // Buscamos el primer encabezado de versión ## [x.x.x]
        if (preg_match('/## \[\d+\.\d+\.\d+\]/', $content)) {
            $content = preg_replace('/(## \[\d+\.\d+\.\d+\])/', $newEntry."\n$1", $content, 1);
        } else {
            // Si no encuentra versiones anteriores, agregarlo después del título principal
            $content = preg_replace('/(# Changelog.*?\n)/s', '$1'.$newEntry, $content, 1);
        }

        file_put_contents($changelogPath, $content);
    }
}
