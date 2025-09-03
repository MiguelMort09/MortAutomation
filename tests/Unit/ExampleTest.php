<?php

use Mort\Automation\Contracts\AutomationInterface;
use Mort\Automation\Traits\ExecutesCommands;

class ExampleCommand implements AutomationInterface
{
    use ExecutesCommands;

    public function handle(): int
    {
        return 0;
    }
}

it('can create a command that implements AutomationInterface', function () {
    $command = new ExampleCommand();
    
    expect($command)
        ->toBeInstanceOf(AutomationInterface::class);
});

it('can execute a command successfully', function () {
    $command = new ExampleCommand();
    
    expect($command->handle())
        ->toBe(0);
});
