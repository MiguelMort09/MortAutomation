<?php

namespace Mort\Automation\Traits;

use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;


trait ExecutesCommands
{
    /**
     * Ejecuta un comando del sistema de forma segura.
     */
    protected function executeCommand(string $command): array
    {
        $this->line("Ejecutando: {$command}");
        
        // Try Laravel Process facade first (Laravel 10+)
        if (class_exists('Illuminate\Support\Facades\Process')) {
            try {
                $result = Process::run($command);
                if ($result->failed()) {
                    $this->error("❌ Error ejecutando: {$command}");
                    $this->error($result->errorOutput());
                    throw new \Exception("Command failed: {$command}");
                }
                $this->info("Comando ejecutado exitosamente");
                if ($result->output()) {
                    $this->line($result->output());
                }
                return [
                    'success' => true,
                    'output' => $result->output(),
                    'error' => $result->errorOutput()
                ];
            } catch (\Exception $e) {
                // Fallback to Symfony Process
                $this->warn("Usando fallback para comando: {$command}");
            }
        }
        
        // Fallback to Symfony Process for older Laravel versions
        $process = new SymfonyProcess(explode(' ', $command));
        $process->run();
        
        if (!$process->isSuccessful()) {
            $this->error("❌ Error ejecutando: {$command}");
            $this->error($process->getErrorOutput());
            throw new \Exception("Command failed: {$command}");
        }
        
        $this->info("Comando ejecutado exitosamente");
        if ($process->getOutput()) {
            $this->line($process->getOutput());
        }
        
        return [
            'success' => true,
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput()
        ];
    }

    /**
     * Ejecuta múltiples comandos en secuencia.
     */
    protected function executeCommands(array $commands): array
    {
        $results = [];
        
        foreach ($commands as $command) {
            $results[] = $this->executeCommand($command);
        }
        
        return $results;
    }

    /**
     * Verifica si un comando existe en el sistema.
     */
    protected function commandExists(string $command): bool
    {
        // Try Laravel Process facade first
        if (class_exists('Illuminate\Support\Facades\Process')) {
            try {
                $result = Process::run("where {$command}"); // For Windows
                if ($result->successful()) {
                    return true;
                }
                $result = Process::run("which {$command}"); // For Linux/macOS
                return $result->successful();
            } catch (\Exception $e) {
                // Fallback to Symfony Process
            }
        }
        
        // Fallback to Symfony Process
        $process = new SymfonyProcess(['where', $command]);
        $process->run();
        if ($process->isSuccessful()) {
            return true;
        }
        
        $process = new SymfonyProcess(['which', $command]);
        $process->run();
        return $process->isSuccessful();
    }
}
