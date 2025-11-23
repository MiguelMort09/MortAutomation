<?php

use Mort\Automation\Contracts\AutomationInterface;
use Mort\Automation\Traits\ExecutesCommands;

it('can create a command that implements AutomationInterface', function () {
    $command = new class implements AutomationInterface {
        use ExecutesCommands;

        public function executeAutomation(): int
        {
            return 0;
        }

        public function isAvailable(): bool
        {
            return true;
        }

        public function getDescription(): string
        {
            return 'Test command';
        }
    };
    
    expect($command)
        ->toBeInstanceOf(AutomationInterface::class);
});

it('can execute a command successfully', function () {
    $command = new class implements AutomationInterface {
        use ExecutesCommands;

        public function executeAutomation(): int
        {
            return 0;
        }

        public function isAvailable(): bool
        {
            return true;
        }

        public function getDescription(): string
        {
            return 'Test command';
        }
    };
    
    expect($command->executeAutomation())
        ->toBe(0);
});
