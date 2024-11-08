<?php

namespace xtcy\ElysiumCore\addons\regions;

use pocketmine\math\Vector3;
use xtcy\ElysiumCore\addons\regions\Region;

class RegionManager {

    private $regions = [];

    public function addRegion(Region $region): void {
        $this->regions[] = $region;
    }

    public function getRegionAt(Vector3 $position): ?Region {
        foreach ($this->regions as $region) {
            if ($region->isWithinBounds($position, $this->regions)) {
                return $region;
            }
        }
        return null;
    }

    public function getRegionByName(string $name): ?Region {
        foreach ($this->regions as $region) {
            if ($region->getName() === $name) {
                return $region;
            }
        }
        return null;
    }
}
