<?php

namespace xtcy\ElysiumCore\tasks;

use pocketmine\level\particle\{
    DustParticle,
    Particle
};
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\world\Position;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class ParticleCircleTask extends Task {
    private Player $player;
    private Vector3 $center;
    private float $radius;
    private float $angle = 0;
    private float $angleIncrement;

    public function __construct(Player $player, Vector3 $center, float $radius, float $angleIncrement = 0.1) {
        $this->player = $player;
        $this->center = $center;
        $this->radius = $radius;
        $this->angleIncrement = $angleIncrement;
    }

    public function onRun(): void {
        $x = $this->center->getX() + $this->radius * cos($this->angle);
        $y = $this->center->getY();
        $z = $this->center->getZ() + $this->radius * sin($this->angle);

        $this->player->getWorld()->addParticle(new Position($x, $y, $z, $this->player->getWorld()), ElysiumUtils::spawnParticle($this->player, "minecraft:falling_dust_top_snow_particle", $x, $y, $z));
    );

        $this->angle += $this->angleIncrement;
    }
}
