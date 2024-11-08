<?php

namespace xtcy\ElysiumCore\tasks;

use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use xtcy\ElysiumCore\addons\envoys\EnvoyManager;
use xtcy\ElysiumCore\addons\envoys\HeroicEnvoy;
use xtcy\ElysiumCore\addons\envoys\NormalEnvoy;
use xtcy\ElysiumCore\Loader;

class SpawnEnvoyTask extends Task {

    private const TOTAL_ENVOYS = 50;

    /** @var World */
    private $world;

    /** @var int */
    private $envoysSpawned = 0;

    public function __construct(World $world) {
        $this->world = $world;
    }

    public function onRun() : void {
        while ($this->envoysSpawned < self::TOTAL_ENVOYS) {
            if ($this->world !== null) {
                $x = mt_rand(1, 500);
                $z = mt_rand(1, 500);
                $y = 4;

                $position = new Position($x, $y, $z, $this->world);
                $this->world->setBlock($position, VanillaBlocks::CHEST()); 
                $text = "&r&5&k: &r&l&dEnvoy &r&5&k:&r\n&r&fTap to open!";
                
                EnvoyManager::create($position, $text, "envoy_" . $position->getX() . "_" . $position->getY() . "_" . $position->getZ());
                $data = mt_rand(0, 1) ? new NormalEnvoy() : new HeroicEnvoy();
                Loader::getBlockDataWorldManager()->get($this->world);

                ++$this->envoysSpawned;
            }
        }
    }
}