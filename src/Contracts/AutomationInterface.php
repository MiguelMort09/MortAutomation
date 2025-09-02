<?php

namespace Mort\Automation\Contracts;

interface AutomationInterface
{
    /**
     * Ejecuta la automatización.
     */
    public function executeAutomation(): int;

    /**
     * Verifica si la automatización está disponible.
     */
    public function isAvailable(): bool;

    /**
     * Obtiene la descripción de la automatización.
     */
    public function getDescription(): string;
}
