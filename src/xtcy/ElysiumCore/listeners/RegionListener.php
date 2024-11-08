<?php

namespace xtcy\ElysiumCore\listeners;

use pocketmine\block\Anvil;
use pocketmine\block\Chest;
use pocketmine\block\DragonEgg;
use pocketmine\block\Hopper;
use pocketmine\block\tile\Beacon;
use pocketmine\block\Trapdoor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityPreExplodeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\item\FireCharge;
use pocketmine\item\FlintSteel;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use xtcy\ElysiumCore\addons\regions\RegionManager;
use pocketmine\utils\TextFormat as C;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class RegionListener implements Listener {

    private $playerRegions = [];

    private RegionManager $regionManager;

    public function __construct(RegionManager $regionManager) {
        $this->regionManager = $regionManager;
    }

    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $currentPosition = $player->getPosition();
        $currentRegion = $this->regionManager->getRegionAt($currentPosition);
    
        $playerName = $player->getName();
        $previousRegion = $this->playerRegions[$playerName] ?? null;
    
        if ($currentRegion !== $previousRegion) {
            if ($currentRegion !== null) {
                $player->sendActionBarMessage(C::colorize("&r&2&l>> &r&a" . $currentRegion->getName()));
            } else {
                $player->sendActionBarMessage(C::colorize("&r&4&l>> &r&cYou have left the region."));
            }
            $this->playerRegions[$playerName] = $currentRegion;
        }
    }
    
    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $region = $this->regionManager->getRegionAt($player->getPosition());

        if (!$player->hasPermission("command.admin")) {
            if ($region !== null && !$region->allowsBlockBreak()) {
                $event->cancel();
                $player->sendMessage(C::colorize("&r&l&cHey! &r&fYou can't do that here!"));
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        $region = $this->regionManager->getRegionAt($player->getPosition());

        if (!$player->hasPermission("command.admin")) {
            if ($region !== null && !$region->allowsBuild()) {
                $event->cancel();
                $player->sendMessage(C::colorize("&r&l&cHey! &r&fYou can't do that here!"));
            }
        }
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event) {
        $player = $event->getDamager();
        
        if ($player instanceof Player) {
            $region = $this->regionManager->getRegionAt($player->getPosition());

            if (!$player->hasPermission("command.admin")) {
                if ($region !== null && !$region->allowsPvp()) {
                    $event->cancel();
                    $player->sendMessage(C::colorize("&r&l&cHey! &r&fYou can't do that here!"));
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event) {
        $player = $event->getEntity();

        if ($player instanceof Player) {
            $region = $this->regionManager->getRegionAt($player->getPosition());

            if ($region !== null && !$region->allowsFallDamage()) {
                if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                    $event->cancel();
                }
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();

        if ($player instanceof Player) {
            $region = $this->regionManager->getRegionAt($player->getPosition());
    
            if ($region !== null && !$region->allowsInteract()) {
                if ($block instanceof DragonEgg || $block instanceof Chest || $block instanceof Hopper || $block instanceof Beacon || $block instanceof Anvil || $item instanceof FlintSteel || $item instanceof FireCharge || $item instanceof Trapdoor) {
                    $event->cancel();
                    $player->sendMessage(C::colorize("&r&l&cHey! &r&fYou can't do that here!"));
                }
            }
        }
    }

    public function onEntityExplode(EntityExplodeEvent $event) {
        $explosionPosition = $event->getPosition();
    
        $region = $this->regionManager->getRegionAt($explosionPosition);
    
        if ($region !== null && !$region->allowsExplosions()) {
            $event->cancel();
        }
    }

    public function onExplosion(EntityPreExplodeEvent $event) {
        $explosionPosition = $event->getEntity()->getPosition();
    
        $region = $this->regionManager->getRegionAt($explosionPosition);
    
        if ($region !== null && !$region->allowsExplosions()) {
            $event->cancel();
            $event->setBlockBreaking(false);
        }
    }

    public function useBucket(PlayerBucketFillEvent $event): void {
        $player = $event->getPlayer();

        if ($player instanceof Player) {
            $region = $this->regionManager->getRegionAt($player->getPosition());

            if ($region !== null && !$region->allowsInteract()) {
                $event->cancel();
                $player->sendMessage(C::colorize("&r&l&cHey! &r&fYou can't do that here!"));
            }
        }
    }

    public function useBucketEmpty(PlayerBucketEmptyEvent $event): void {
        $player = $event->getPlayer();

        if ($player instanceof Player) {
            $region = $this->regionManager->getRegionAt($player->getPosition());

            if ($region !== null && !$region->allowsInteract()) {
                $event->cancel();
                $player->sendMessage(C::colorize("&r&l&cHey! &r&fYou can't do that here!"));
            }
        }
    }

    public function useCommand(CommandEvent $event): void {
        $sender = $event->getSender();
        $command = $event->getCommand();

        if ($sender instanceof Player && strtolower($command) === "f claim") {
            $region = $this->regionManager->getRegionAt($sender->getPosition());
            if ($region !== null) {
                $sender->sendMessage(C::RED . "You cannot claim territory while inside a region.");
                $event->cancel();
            }
        }
    }

}