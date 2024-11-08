<?php

namespace xtcy\ElysiumCore\tasks;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\event\entity\EntityRegainHealthEvent;
use xtcy\ElysiumCore\listeners\EnchantListener;

class AngelicHealingTask extends Task {

    /** @var Player */
    private Player $victim;

    private float $healingAmount;

    public function __construct(Player $victim, float $healingAmount) {
        $this->victim = $victim;
        $this->healingAmount = $healingAmount;
    }

    public function onRun(): void {
        if ($this->victim->isOnline() && !$this->victim->isClosed()) {
            $this->victim->heal(new EntityRegainHealthEvent($this->victim, $this->healingAmount, EntityRegainHealthEvent::CAUSE_MAGIC));
        } else {
            $this->getHandler()->cancel();
        }
    }
    public function onCancel(): void {
      unset(EnchantListener::$activeAngelicTasks[$this->victim->getName()]);
    }
}
