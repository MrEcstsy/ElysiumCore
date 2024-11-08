<?php

namespace xtcy\ElysiumCore\tasks;

use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\SmokeParticle;
use wockkinmycup\utilitycore\utils\Utils;

class CrystalParticleTask extends Task {

    private $entity;

    public function __construct(Living $entity) {
        $this->entity = $entity;
    }

    public function onRun(): void {
        $level = $this->entity->getWorld();
        $pos = $this->entity->getPosition();

        $beamHeight = 30; 
        $beamDensity = 6; 

        $startX = $pos->x;
        $startY = $pos->y + 4; 
        $startZ = $pos->z;

        for ($i = 0; $i < $beamHeight; $i++) {
            $particleX = $startX;
            $particleY = $startY + $i;
            $particleZ = $startZ;

            for ($j = 0; $j < $beamDensity; $j++) {
                $offsetX = mt_rand(-2, 2);
                $offsetZ = mt_rand(-2, 2);

                $particlePosX = $particleX + $offsetX;
                $particlePosY = $particleY;
                $particlePosZ = $particleZ + $offsetZ;

                $particlePos = new Vector3($particlePosX, $particlePosY, $particlePosZ);
                $particle = new SpawnParticleEffectPacket();
                $particle->particleName = "minecraft:egg_destroy_emitter";
                $particle->position = $particlePos;

                $level->broadcastPacketToViewers($pos, $particle);
                
            }
        }
    }
}
