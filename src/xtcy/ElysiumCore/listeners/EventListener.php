<?php

namespace xtcy\ElysiumCore\listeners;

use DaPigGuy\PiggyFactions\PiggyFactions;
use DaPigGuy\PiggyFactions\players\PlayerManager as PlayersPlayerManager;
use DaPigGuy\PiggyFactions\utils\Relations;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\addons\brag\Brag;
use xtcy\ElysiumCore\addons\regions\RegionManager;
use xtcy\ElysiumCore\enchants\vanilla\DepthStriderEnchantment;
use xtcy\ElysiumCore\enchants\vanilla\LootingEnchantment;
use xtcy\ElysiumCore\entities\AstralCrystal;
use xtcy\ElysiumCore\entities\AstralRanger;
use xtcy\ElysiumCore\entities\FloatingTextEntity;
use xtcy\ElysiumCore\entities\HollowCrystal;
use xtcy\ElysiumCore\entities\HollowGuardian;
use xtcy\ElysiumCore\entities\SoulCrystal;
use xtcy\ElysiumCore\entities\SoulGuardian;
use xtcy\ElysiumCore\items\Items;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\player\PlayerManager;
use xtcy\ElysiumCore\utils\ElysiumUtils;
use xtcy\spawnerv1\entity\EntityManager;

class EventListener implements Listener
{

    private RegionManager $regionManager;

    private array $hitCounts = [];

    public function __construct(RegionManager $regionManager) {
        $this->regionManager = $regionManager;
    }

    public function onPlace(BlockPlaceEvent $event): void {
        $block = $event->getItem();
        $tag = $block->getNamedTag();

        $tags = ["crate_key", "boss", "lootbox", "custom_items", "drop_package"];
        foreach ($tags as $t) {
            if ($tag->getTag($t)) {
                $event->cancel();
            }
        }
    }

    /**
     * @throws \JsonException
     */
    public function onLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        if (PlayerManager::getInstance()->getSession($player) === null) {
            PlayerManager::getInstance()->createSession($player);
        }
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $session = Loader::getPlayerManager()->getSession($player);

        PlayerManager::getInstance()->getSession($player)->setConnected(true);
        $event->setJoinMessage("");

        $joinMessage = [
            "&r&8&l      -----------< " . Loader::SERVER_NAME . "&l&8 >-----------",
            "&r&7         ➥ &fHome of the Elysian Population",
            "",
            "              &r&l&5Server Information: " . Loader::SERVER_NAME,
            "                    &r&7➥ &fAccount: " . $player->getNameTag(),
            "                    &r&7➥ &fConnected: &a" . count(Server::getInstance()->getOnlinePlayers()) . " player(s)",
            "                    &r&7➥ &fWebstore: etherealhub.net",
            "                    &r&7➥ &fDiscord: discord.etherealhub.net",
            "                    &r&7➥ &fVote: vote.etherealhub.net",
            "",
            "            &r&7&o( For additional support, join our Discord )",
        ];

        foreach ($joinMessage as $message) {
            $player->sendMessage(C::colorize($message));
        }

        if (!$player->hasPlayedBefore()) {
            $settings = [
                "chest_inventories" => true,
                "announcer" => true,
                "server_effects" => true,
                "lootbox_broadcast" => true
            ];
            foreach ($settings as $key => $value) {
                PlayerManager::getInstance()->getSession($player)->setSetting($key, $value);
            }

            $players = Server::getInstance()->getOnlinePlayers();

            foreach ($players as $oplayer) {
                $oplayer->sendMessage(C::colorize("&r&d&l(!) &r&7" . $player->getName() . " &7has joined for the first time!"));
            }
        }

        ElysiumUtils::sendUpdate($player);  
        $dp = ["simple", "unique", "elite", "ultimate", "legendary"];

        foreach ($dp as $key) {
            $player->getInventory()->addItem(Items::createDropPackage($key));
        }
    }

    public function onUse(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $session = Loader::getPlayerManager()->getSession($player);
    
        if ($item->getTypeId() === ItemTypeIds::ENDER_PEARL) {
            if ($session->getCooldown("enderpearl") === 0 || $session->getCooldown("enderpearl") === null) {
                $session->addCooldown("enderpearl", 16);
            } else {
                $event->cancel();
                $player->sendMessage(C::colorize("&r&c&l(!) &r&7You can use this again in " . Utils::translateTime($session->getCooldown("enderpearl"))));
            }
        }
    }
    
    public function onEat(PlayerItemConsumeEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $session = Loader::getPlayerManager()->getSession($player);
    
        if ($item->getTypeId() === ItemTypeIds::GOLDEN_APPLE) {
            if ($session->getCooldown("goldenapple") === 0 || $session->getCooldown("goldenapple") === null) {
                $session->addCooldown("goldenapple", 15);
            } else {
                $event->cancel();
                $player->sendMessage(C::colorize("&r&c&l(!) &r&7You can't eat this again for " . Utils::translateTime($session->getCooldown("goldenapple"))));
            }
        } else if ($item->getTypeId() === ItemTypeIds::ENCHANTED_GOLDEN_APPLE) {
            if ($session->getCooldown("enchantedgoldenapple") === 0 || $session->getCooldown("enchantedgoldenapple") === null) {
                $session->addCooldown("enchantedgoldenapple", 60);
            } else {
                $event->cancel();
                $player->sendMessage(C::colorize("&r&c&l(!) &r&7You can't eat this again for " . Utils::translateTime($session->getCooldown("enchantedgoldenapple"))));
            }
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof Player) {
                $damagerSession = Loader::getPlayerManager()->getSession($damager);
                $victimSession = Loader::getPlayerManager()->getSession($player);
                $damagerSession->addKills();
                $handItem = $damager->getInventory()->getItemInHand();

                if ($handItem->getNamedTag()->getTag("scrolls") !== null) {
                    $value = $handItem->getNamedTag()->getString("scrolls");
                    if ($value === "killcounter") {
                        $lore = $handItem->getLore();
                        foreach ($lore as $index => $line) {
                            if (strpos($line, "§r§ePlayer Kills: §6") === 0) {
                                $parts = explode("§6", $line);
                                $currentKills = (int) end($parts);
                                $newKills = $currentKills + 1;
                                $lore[$index] = "§r§ePlayer Kills: §6" . $newKills;
                                $handItem->setLore($lore);
                                $damager->getInventory()->setItemInHand($handItem);
                                break;
                            }
                        }
                    }
                }

                $damager->sendTitle(C::colorize("&r&l&cKILLED &r&f" . $player->getName()));
                $event->setDeathMessage(C::colorize("&r&c" . $player->getName() . "&4[" . $victimSession->getKDRRatio() . "]&r&f was slain by &c" . $damager->getName() . "&4[" . $damagerSession->getKDRRatio() . "]&r&f using &r" . $handItem->getName()));
            }
        }
        $victimSession = Loader::getPlayerManager()->getSession($player);
        $victimSession->addDeaths();

        $xpbottle = Items::createExperienceBottle($player, $player->getXpManager()->getCurrentTotalXp());
        
        if ($player->getXpManager()->getCurrentTotalXp() > 0) {
            $player->getWorld()->dropItem($player->getPosition(), $xpbottle);
            $event->setXpDropAmount(0);
        } elseif ($player->getXpManager()->getCurrentTotalXp() === 0) {
            return;
        }
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        PlayerManager::getInstance()->getSession($player)->setConnected(false);

    }

    public function onHit(EntityDamageByEntityEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof FloatingTextEntity) {
            $event->cancel();
        }

        if ($entity instanceof HollowCrystal || $entity instanceof SoulCrystal || $entity instanceof AstralCrystal || $entity instanceof SoulGuardian || $entity instanceof AstralRanger || $entity instanceof HollowGuardian) {
            ElysiumUtils::updateEntityNameTag($entity);
        }
    }

    public function onEntityRegainHealth(EntityRegainHealthEvent $event): void {
		$entity = $event->getEntity();
        if ($entity instanceof HollowCrystal || $entity instanceof SoulCrystal || $entity instanceof AstralCrystal || $entity instanceof SoulGuardian || $entity instanceof AstralRanger || $entity instanceof HollowGuardian) {
            ElysiumUtils::updateEntityNameTag($entity);
        }
	}

    public function onEntityDamage(EntityDamageByEntityEvent $event): void
    {
        $damager = $event->getDamager();
        $target = $event->getEntity();
        $damage = $event->getFinalDamage();
    
        if ($damager === null || $target === null) {
            return;
        }
    
        if ($damager instanceof Player && $damager->isFlying()) {
            ElysiumUtils::toggleFlight($damager, true);
        }
    
        if ($target instanceof Player && $target->isFlying()) {
            ElysiumUtils::toggleFlight($target, true);
        }
    
        $damagerRegion = $this->regionManager->getRegionAt($damager->getPosition());
        $targetRegion = $this->regionManager->getRegionAt($target->getPosition());
    
        if (($damagerRegion !== null && $damagerRegion->getName() === "Spawn") || ($targetRegion !== null && $targetRegion->getName() === "Spawn")) {
            return;
        }

        if ($damager instanceof Player) {
            $damagerName = $damager->getName();
            if (!isset($this->hitCounts[$damagerName])) {
                $this->hitCounts[$damagerName] = 0;
            }

            if ($damager instanceof Player && $target instanceof Player) {
                if ($damager->isSprinting()) {
                    $event->setKnockBack(0.6);
                    $event->setVerticalKnockBackLimit($target->isOnGround() ? 0.44 : 0.5);
                } else {
                    $event->setKnockBack(0.4);
                    $event->setVerticalKnockBackLimit($target->isOnGround() ? 0.36 : 0.8);
                }
            }
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $killer = $event->getDamager();
        if ($killer instanceof Player) {
            $item = $killer->getInventory()->getItemInHand();
            $lootingEnchantment = new LootingEnchantment();
            if (($level = $killer->getInventory()->getItemInHand()->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId($lootingEnchantment->getMcpeId()))) > 0) {
                if (
                    !$player instanceof Player and
                    $player instanceof Living and
                    $event->getFinalDamage() >= $player->getHealth()
                ) {
                    $add = mt_rand(0, $level + 1);
                    if (is_bool(Utils::getConfiguration(Loader::getInstance(), "config.yml")->get("looting.entities"))) {
                        Server::getInstance()->getLogger()->debug("There is an error (looting) in the config of vanillaEC");
                        return;
                    }
                    $lootingMultiplier = Utils::getConfiguration(Loader::getInstance(), "config.yml")->get("looting.drop_multiplier", 1); // Drop multiplier from config

                    foreach (Utils::getConfiguration(Loader::getInstance(), "config.yml")->get("looting.entities", []) as $items) {
                        $items = [];

                        $drops = $this->getLootingDrops($player->getDrops(), $items, $add, $lootingMultiplier);
                        foreach ($drops as $drop) {
                            $killer->getWorld()->dropItem($player->getPosition()->asVector3(), $drop);
                        }
                        $player->flagForDespawn();
                    }
                }
            }
        }
    }

    /**
     * @param array $drops
     * @param array $items
     * @param int   $add
     * @param int   $multiplier
     * @return array
     */
    public function getLootingDrops(array $drops, array $items, int $add, int $multiplier): array
    {
        $lootingDrops = [];

        foreach ($items as $item2) {
            $item = StringToItemParser::getInstance()->parse($item2);
            /** @var Item $drop */
            foreach ($drops as $drop) {
                if ($drop->equals($item)) {
                    $drop->setCount($drop->getCount() + ($add * $multiplier));
                }
                $lootingDrops[] = $drop;
                break;
            }
        }

        return $lootingDrops;
    }

    /**
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getArmorInventory()->getBoots();
        $depthStriderEnchantment = new DepthStriderEnchantment();
        $speed = $player->getMovementSpeed();

        if ($item !== null && $item->hasEnchantment(EnchantmentIdMap::getInstance()->fromId($depthStriderEnchantment->getMcpeId()))) {
            $level = $item->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId($depthStriderEnchantment->getMcpeId()));

            if ($player->isSwimming()) {
                $speed = 0.1 * (1 + 0.3333 * $level);
                $player->setMovementSpeed($speed);
            } elseif ($player->isUnderwater()) { // TODO: Reset the speed after being underwater
                //$player->setMovementSpeed($speed);
            }
        }
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
		$hand = $event->getPlayer()->getInventory()->getItemInHand();
        $facPlayer = PlayersPlayerManager::getInstance()->getPlayer($player);

		if (strpos($message, "[brag]") !== false) {
			if (Brag::isBragging($player)) {
				$player->sendMessage("§r§c§l(!) §r§cPlease wait a while before using [BRAG] !");
				$event->cancel();
				return;
			}
            
			Brag::setBragging($player);
			$event->setMessage(str_replace("[brag]", C::colorize("&r&o&6{$event->getPlayer()->getName()}'s Inventory &r"), $message));
			return;
		}

		if (strpos($message, "[item]") !== false) {
			$customname = $hand->hasCustomName() ? $hand->getCustomName() : $hand->getVanillaName();
			$count = $hand->getCount();
			$event->setMessage(str_replace("[item]", C::colorize("&r&l&f» &r$customname &r&7({$count}x) &r&l&f« &r"), $message));
		}
    }
}    