<?php

namespace xtcy\ElysiumCore\tasks;

use pocketmine\scheduler\Task;
use xtcy\ElysiumCore\Loader;

class UpdateTopFactionsTask extends Task {

    private Loader $plugin;

    public function __construct(Loader $loader)
    {
        $this->plugin = $loader;
    }

    public function onRun(): void
    {
        
    }
}