<?php

namespace xtcy\ElysiumCore\entities;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use xtcy\ElysiumCore\items\Items;

class BlazeFury extends Living {

    public const NETWORK_ID = EntityIds::BLAZE;
    /** @var float $width */
    public float $width = 0.875;
    /** @var float $height */
    public float $height = 2.0;
    public ?Player $target = null;

    protected bool $persistent = false;
    public int $x = 0;
    public int $z = 0;
	public int $jumpTicks = 0;

    /** @var array */
    private $damageTracker = [];

    public function initEntity(CompoundTag $nbt): void {
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTagVisible(true);
        $this->setHealth(1500);
        $this->setMaxHealth(1500);
        $this->setNameTag($this->getNameTag());

        parent::initEntity($nbt);

    }

    public function getName(): string
    {
        return "Blaze Fury";
    }

    public function getNameTag(): string
    {
        return "§r§l§eBlaze Fury \n" . $this->getHealthBar();
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo($this->height, $this->width);
    }

    public static function getNetworkTypeId(): string
    {
        return self::NETWORK_ID;
    }

    protected function getInitialDragMultiplier(): float {
        return 0.05;
    }

    protected function getInitialGravity(): float {
        return 0.08;
    }
    
    public function entityBaseTick(int $tickDiff = 1): bool {
        parent::entityBaseTick($tickDiff);
    
        if (!$this->hasTarget()) {
            $this->target = $this->findClosestPlayer();
        }
    
        if ($this->hasTarget()) {
            $this->followTarget();
            if ($this->target !== null) {
                $this->lookAt($this->target->getPosition()->add(0, $this->getEyeHeight(), 0));
    
                $playersInRange = $this->getPlayersInRange(1); 
                foreach ($playersInRange as $player) {
                   $this->attackPlayer($player);
                }
            }
        } else {
            $this->lookAt($this->getPosition()->add($this->getMotion()->x, 0, $this->getMotion()->z));
        }
    
        if ($this->shouldJump()) {
            $this->jump();
        }
    
        return true;
    }

    
    
    public function getPlayersInRange(float $radius): array {
        $players = [];
        foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy($radius, $radius, $radius), $this) as $entity) {
            if ($entity instanceof Player) {
                $players[] = $entity;
            }
        }
        return $players;
    }
    
    
    public function jump(): void{
        $this->motion->y = $this->gravity * $this->getJumpMultiplier();
        $this->move($this->motion->x * 0.01, $this->motion->y, $this->motion->z * 0.01);
    }
    
    public function findClosestPlayer(): ?Player {
        $closestDistance = 15;
        $closestPlayer = null;
    
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player instanceof Player) {
                $distance = $player->getLocation()->distance($this->getLocation());
                if ($distance < $closestDistance) {
                    $closestDistance = $distance;
                    $closestPlayer = $player;
                }
            }
        }
    
        return $closestPlayer;
    }
    
    public function clearTarget(): void {
        $this->target = null;
    }
    

    public function attackPlayer(Player $player): void {
        $direction = $player->getPosition()->subtract($this->getPosition()->getX(), $this->getPosition()->getY(), $this->getPosition()->getZ())->normalize();

        $knockbackX = $direction->x;
        $knockbackZ = $direction->z;        

    
        $player->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 3));
    
    }
    

    public function setTarget(Player $player): void {
        $this->target = $player;
    }

    public function hasTarget(): bool {
        return $this->target instanceof Player;
    }

    public function onCollideWithEntity(Entity $entity): void {
    }

    /**
     * @param Block $block
     */
    public function onCollideWithBlock(Block $block): void {
    }

    public function setPersistence(bool $persistent): self
    {
        $this->persistent = $persistent;
        return $this;
    }

    public function followTarget(): void {
        $target = $this->target;
        if ($target instanceof Player) {
            $distance = $target->getPosition()->distance($this->getPosition());
            if ($distance <= 10) {
                $direction = $target->getPosition()->subtract($this->getPosition()->getX(), $this->getPosition()->getY(), $this->getPosition()->getZ())->normalize();
                $this->motion->x = $direction->x * 0.2;
                $this->motion->y = $this->motion->y;
                $this->motion->z = $direction->z * 0.2;
            } else {
                $this->clearTarget();
            }
        }
    }
    
    
    public function shouldJump(): bool {
        $frontBlock = $this->getFrontBlock();
        
        if ($frontBlock->isSolid()) {
            return true;
        } elseif ($frontBlock instanceof Stair || $frontBlock instanceof Slab) {
            return true;
        }
        
        return false;
    }
    
    
    public function getFrontBlock($y = 0): Block {
        $directionVector = $this->getDirectionVector();
        $position = $this->getPosition()->add($directionVector->x, $y, $directionVector->z)->floor();
        return $this->getWorld()->getBlockAt($position->x, $position->y, $position->z);
    }    

    public function getJumpMultiplier(): int {
        $frontBlock = $this->getFrontBlock();
        $belowBlock = $this->getFrontBlock(-0.5);
        $belowFrontBlock = $this->getFrontBlock(-1);
    
        if ($frontBlock->isSolid()) {
            return 3; 
        }
    
        if ($frontBlock instanceof Slab || $belowBlock instanceof Slab || $belowFrontBlock instanceof Slab) {
            return 10; 
        }
    
        if ($frontBlock instanceof Stair || $belowBlock instanceof Stair || $belowFrontBlock instanceof Stair) {
            return 10; 
        }
    
        return 5; 
    }
    
    public function kill(): void
    {
        parent::kill();
    
        arsort($this->damageTracker);
    
        $rewardCount = min(count($this->damageTracker), 3);
    
        $rewardIndex = 0;
        $topPlayersMessage = "&r&8----------[ &e&lBlaze Fury &r&8]----------\n";
        foreach ($this->damageTracker as $playerName => $damage) {
            if ($rewardIndex >= $rewardCount) {
                break;
            }
            $rewardIndex++;
            
            if ($player = Server::getInstance()->getPlayerExact($playerName)) {
                // Calculate the damage percentage
                $damagePercentage = ($damage / $this->getMaxHealth()) * 100;
                $damagePercentage = min(100, $damagePercentage); 
                
                $player->getInventory()->addItem(Items::getCrateKey("empyrean", 1));
                $player->sendMessage(Textformat::colorize("&r&l&f(!) &r&fYou dealt &b" . round($damagePercentage, 2) . "%&r&f damage to the &eBlaze Fury&r!"));
                $topPlayersMessage .= "\n&r&6&l{$rewardIndex}. &r&e{$playerName} &8(&f" . round($damagePercentage, 2) . "%&8)";
            }
        }
        
        Server::getInstance()->broadcastMessage(TextFormat::colorize($topPlayersMessage));

    }

    public function attack(EntityDamageEvent $source): void {
        parent::attack($source);
    
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                $damagerName = $damager->getName();
    
                if (!isset($this->damageTracker[$damagerName])) {
                    $this->damageTracker[$damagerName] = 0;
                }
    
                $this->damageTracker[$damagerName] += $source->getFinalDamage();
    
            }
        }
    }
    
    public function getBoundingBox(): AxisAlignedBB {
        return $this->boundingBox;
    }

    public function canCollideWith(Entity $entity): bool {
        return true;
    }

    private function getHealthBar(): string {
        $totalBars = 20; 
        $currentHealth = $this->getHealth();
        $maxHealth = $this->getMaxHealth();
        $currentHealthBars = (int) round(($currentHealth / $maxHealth) * $totalBars);

        $healthBar = "";
        for ($i = 0; $i < $totalBars; $i++) {
            $healthColor = $this->getHealthColor(ceil(($currentHealth / $maxHealth) * 4));
            $healthBar .= $i < $currentHealthBars ? $healthColor . "|" : "§c|";
        }

        return $healthBar;
    }

    private function getHealthColor(int $healthMultiplier): string {
        switch ($healthMultiplier) {
            case 1:
                return "§a"; 
            case 2:
                return "§e"; 
            case 3:
                return "§c"; 
            default:
                return "§6"; 
        }
    }
}
    