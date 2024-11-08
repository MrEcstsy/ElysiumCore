<?php

namespace xtcy\ElysiumCore\entities;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\sound\BlockBreakSound;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\tasks\CrystalParticleTask;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class HollowCrystal extends Living {

    public const NETWORK_ID = EntityIds::ENDER_CRYSTAL;

    /** @var float $width */
    public float $width = 1;

    /** @var float $height */
    public float $height = 2.0;
    
    /** @var array */
    private array $damageTracker = [];

    public TaskHandler $task;

    private int $tickCounter = 0;

    public function initEntity(CompoundTag $nbt): void {
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTagVisible(true);
        $this->setHealth(1450);
        $this->setMaxHealth(1450);
        $this->setNameTag($this->getNameTag());
        $this->setScale(2);
        $this->setNoClientPredictions(true);

        parent::initEntity($nbt);

        $this->task = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new CrystalParticleTask($this), 20);
    }

    public function getName(): string
    {
        return "Hollow Crystal";
    }

    public function getNameTag(): string
    {
        return "§r§l§fHollow Core \n" . $this->getHealthBar();
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

        $this->tickCounter += $tickDiff;

        if ($this->tickCounter >= 200) {
            $this->tickCounter = 0;
            $this->playSpawnSound();

            $guardian = new HollowGuardian($this->getLocation());
            $guardian->spawnToAll();
        }

        $this->setNameTag($this->getNameTag());
        $this->setNameTagAlwaysVisible(true);
        return true;
    }

    private function playSpawnSound(): void {
        Utils::playSound($this, "portal.travel");
    }

    public function spawnDeathParticles(): void {
        $world = $this->getWorld();
        $pos = $this->getPosition();

        $world->addParticle($pos->asVector3(), new HugeExplodeParticle());
        
    }

    public function kill(): void
    {
        parent::kill();
    
        $this->spawnDeathParticles();
        $this->getWorld()->addSound($this->getPosition()->asVector3(), new BlockBreakSound(VanillaBlocks::GLASS()));
        if ($this->task !== null) {
            $this->task->cancel();
        }

        arsort($this->damageTracker);
    
        $rewardCount = min(count($this->damageTracker), 3);
    
        $rewardIndex = 0;
        foreach ($this->damageTracker as $playerName => $damage) {
            if ($rewardIndex >= $rewardCount) {
                break;
            }
            $rewardIndex++;
            
            if ($player = Server::getInstance()->getPlayerExact($playerName)) {

                $reward = ElysiumUtils::getRewardForPlace($rewardIndex, "hollow");

                $player->getInventory()->addItem($reward);
            }
        }
        
        $messages = [
            "               &r&f&l<&r&fIncursion&f: &fHollow&r&f&l>",
            "&r&7&o The Hollow Guardians' relentless attack has finally ended.",
            "&r&7&o  Reality breathes a sigh of relief as their chaos fades away.",
            "&r&7&o   The rift they emerged from closes, restoring order to the realm.",
        ];
        
        foreach ($messages as $message) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(TextFormat::colorize($message));
            }
        }

        $this->damageTracker = [];
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

    public function knockBack(float $x = 0, float $z = 0, float $force = 0, ?float $verticalLimit = 0): void
    {
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
        $missingHealthBars = $totalBars - $currentHealthBars;

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