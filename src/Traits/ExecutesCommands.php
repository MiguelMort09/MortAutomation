<?php

namespace Mort\Automation\Traits;

use Illuminate\Process\Process;

trait ExecutesCommands
{
    /**
     * Ejecuta un comando del sistema de forma segura.
     */
    protected function executeCommand(string $command): \Illuminate\Process\ProcessResult
    {
        $this->line("Ejecutando: {$command}");
        
        $result = Process::run($command);
        
        if ($result->failed()) {
            $this->error("Error ejecutando comando: {$command}");
            $this->error($result->errorOutput());
        } else {
            $this->info("Comando ejecutado exitosamente");
            if ($result->output()) {
                $this->line($result->output());
            }
        }
        
        return $result;
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
        $result = Process::run("where {$command}");
        return $result->successful();
    }
}
