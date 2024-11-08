<?php

namespace xtcy\ElysiumCore\listeners;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use xtcy\ElysiumCore\items\Items;
use xtcy\ElysiumCore\Loader;

class QuestListener implements Listener {

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $session = Loader::getPlayerManager()->getSession($player);
        $quests = $session->getAllQuests(); 
    
        foreach ($quests as $questName => $questData) {
            if ($questData['task'] === 'mine') {
                $progress = $questData['progress'];
                $target = $questData['target'];
                $targetType = $questData['target_type'];
                
                if ($progress < $target) {
                    $blockName = strtolower($block->getName());
                    if ($targetType === 'all' || $targetType === $blockName) {
                        $newProgress = min($progress + 1, $target);
                        $session->incrementQuestProgress($questName, 'progress', $newProgress - $progress);
                        if ($newProgress === $target) {
                            $player->sendMessage(C::colorize("&r&l&a(!) &r&a Completed quest '" . $questName . "'"));
                        }
                    }
                }
            } elseif ($questData['task'] === 'harvest') {
                $progress = $questData['progress'];
                $target = $questData['target'];
                $targetType = $questData['target_type'];
                
                if ($progress < $target) {
                    if ($targetType === 'all' || $targetType === $block->getTypeId()) {
                        $newProgress = min($progress + 1, $target);
                        $session->incrementQuestProgress($questName, 'progress', $newProgress - $progress);
                        if ($newProgress === $target) {
                            $player->sendMessage(C::colorize("&r&l&a(!) &r&a Completed quest '" . $questName . "'"));
                        }
                    }
                }
            }
        }    
    }
 
    public function onEntityDeath(EntityDeathEvent $event): void {
        $entity = $event->getEntity();
        $cause = $entity->getLastDamageCause();
    
        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof Player) {
                $session = Loader::getPlayerManager()->getSession($damager);
                $quests = $session->getAllQuests();
    
                foreach ($quests as $questName => $questData) {
                    if ($questData['task'] === 'kill') {
                        $target = $questData['target'];
                        $targetType = $questData['target_type'];
    
                        if ($targetType === 'mobs' || $targetType === strtolower($entity->getName())) {
                            $currentProgress = $questData['progress'];
                            if ($currentProgress < $target) {
                                $newProgress = min($currentProgress + 1, $target);
                                $session->incrementQuestProgress($questName, 'progress', $newProgress - $currentProgress);
                                if ($newProgress >= $target) {
                                    $damager->sendMessage(C::GREEN . C::BOLD . "(!) " . C::RESET . "Completed quest '" . $questName . "'");
                                }
                            }
                        }
                    } elseif ($questData['task'] === 'gain_xp') {
                        $target = $questData['target'];
                        $targetType = $questData['target_type'];
    
                        if ($targetType === 'xp') {
                            $currentProgress = $questData['progress'];
                            $newProgress = min($currentProgress + $entity->getXpDropAmount(), $target);
                            $session->incrementQuestProgress($questName, 'progress', $newProgress - $currentProgress);
    
                            if ($newProgress >= $target) {
                                $damager->sendMessage(C::GREEN . C::BOLD . "(!) " . C::RESET . "Completed quest '" . $questName . "'");
                            }
                        }
                    }
                }
            }
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $cause = $event->getPlayer()->getLastDamageCause();
        
        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof Player) {
                $session = Loader::getPlayerManager()->getSession($damager);
                $quests = $session->getAllQuests();
                foreach ($quests as $questName => $questData) {
                    if ($questData['task'] === 'kill') {
                        $target = $questData['target'];
                        $targetType = $questData['target_type'];

                        if ($targetType === 'player') {
                            $newProgress = min($questData['progress'] + 1, $target);
                            $session->incrementQuestProgress($questName, 'progress', $newProgress - $questData['progress']);
                            if ($newProgress >= $target) {
                                $damager->sendMessage(C::colorize("&r&l&a(!) &r&a Completed quest '" . $questName . "'"));
                            }
                        }
                    }
                }
            }
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $session = Loader::getPlayerManager()->getSession($player);
        $quests = $session->getAllQuests();

        foreach ($quests as $questName => $questData) {
            if ($questData['task'] === 'travel') {
                $progress = $questData['progress'];
                $target = $questData['target'];
                
                if ($progress < $target) {
                    $distance = round($event->getFrom()->distance($event->getTo())); 
                    if ($distance > 0) {
                        $newProgress = min($progress + $distance, $target);
                        $session->incrementQuestProgress($questName, 'progress', $newProgress - $progress);
                        if ($newProgress >= $target) {
                            $player->sendMessage(C::colorize("&r&l&a(!) &r&a Completed quest '" . $questName . "'"));
                        }
                    }
                }
            }
        }
    }
    
    public function onPlayerExperienceChange(PlayerExperienceChangeEvent $event): void {
        $player = $event->getEntity();
        $newXp = $event->getNewProgress();
        $oldXp = $event->getOldProgress();
        $xpChange = $oldXp - $newXp; 

        if ($player instanceof Player) {
            if ($xpChange > 0) {
                $session = Loader::getPlayerManager()->getSession($player);
                $quests = $session->getAllQuests();

                foreach ($quests as $questName => $questData) {
                    if ($questData['task'] === 'spend_xp') {
                        $progress = $questData['progress'];
                        $target = $questData['target'];
                        $newProgress = min($progress + $xpChange, $target);
                        $session->incrementQuestProgress($questName, 'progress', $newProgress - $progress);

                        if ($newProgress >= $target) {
                            $player->sendMessage(C::colorize("&r&l&a(!) &r&a Completed quest '" . $questName . "'"));
                        }
                    }
                }
            }
        }
    }
}    