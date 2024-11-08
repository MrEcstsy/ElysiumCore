<?php

namespace xtcy\ElysiumCore\addons\regions;

use pocketmine\math\Vector3;

class Region {
    private string $name;
    private Vector3 $pos1;
    private Vector3 $pos2;
    private bool $allowPvp;
    private bool $allowBlockBreak;
    private bool $allowFly;
    private bool $allowFallDamage;
    private bool $allowBuild;
    private bool $allowInteract;
    private bool $allowExplosions;

    public function __construct(string $name, Vector3 $pos1, Vector3 $pos2, bool $allowPvp, bool $allowBlockBreak, bool $allowFly, bool $allowFallDamage, bool $allowBuild, bool $allowInteract, bool $allowExplosions) {
        $this->name = $name;
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
        $this->allowPvp = $allowPvp;
        $this->allowBlockBreak = $allowBlockBreak;
        $this->allowFly = $allowFly;
        $this->allowFallDamage = $allowFallDamage;
        $this->allowBuild = $allowBuild;
        $this->allowInteract = $allowInteract;
        $this->allowExplosions = $allowExplosions;
    }

    public function isInside(Vector3 $position): bool {
        return $position->x >= min($this->pos1->x, $this->pos2->x) && $position->x <= max($this->pos1->x, $this->pos2->x)
            && $position->y >= min($this->pos1->y, $this->pos2->y) && $position->y <= max($this->pos1->y, $this->pos2->y)
            && $position->z >= min($this->pos1->z, $this->pos2->z) && $position->z <= max($this->pos1->z, $this->pos2->z);
    }

    public function getName(): string {
        return $this->name;
    }

    public function allowsPvp(): bool {
        return $this->allowPvp;
    }

    public function allowsBlockBreak(): bool {
        return $this->allowBlockBreak;
    }

    public function allowsFlight(): bool {
        return $this->allowFly;
    }

    public function allowsFallDamage(): bool {
        return $this->allowFallDamage;
    }

    public function allowsBuild(): bool {
        return $this->allowBuild;
    }

    public function allowsInteract(): bool {
        return $this->allowInteract;
    }

    public function allowsExplosions(): bool {
        return $this->allowExplosions;
    }

    public function isWithinBounds(Vector3 $position): bool {
        return $position->x >= min($this->pos1->x, $this->pos2->x) &&
               $position->x <= max($this->pos1->x, $this->pos2->x) &&
               $position->y >= min($this->pos1->y, $this->pos2->y) &&
               $position->y <= max($this->pos1->y, $this->pos2->y) &&
               $position->z >= min($this->pos1->z, $this->pos2->z) &&
               $position->z <= max($this->pos2->z, $this->pos2->z);
    }
}
