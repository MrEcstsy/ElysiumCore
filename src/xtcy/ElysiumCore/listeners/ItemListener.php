<?php

namespace xtcy\ElysiumCore\listeners;

use IvanCraft623\RankSystem\RankSystem;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Pickaxe;
use pocketmine\item\StringToItemParser;
use pocketmine\item\Sword;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\addons\incursions\IncursionManager;
use xtcy\ElysiumCore\addons\regions\RegionManager;
use xtcy\ElysiumCore\enchants\util\CustomEnchantment;
use xtcy\ElysiumCore\enchants\util\CustomEnchantments;
use xtcy\ElysiumCore\entities\AncientGuardian;
use xtcy\ElysiumCore\entities\BlazeFury;
use xtcy\ElysiumCore\entities\BroodMother;
use xtcy\ElysiumCore\items\Items;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\ElysiumUtils;
use xtcy\ElysiumCore\utils\EnchantUtils;
use xtcy\ElysiumCore\utils\Menu\DropPackages;
use xtcy\ElysiumCore\utils\RarityType;

class ItemListener implements Listener
{

    /** @var array $itemRenamer */
	public array $itemRenamer = [];

	/** @var array $lorerenamer */
	public array $lorerenamer = [];

    /** @var array $messages */
	public array $messages = [];

    /** @var array $message */
    public array $message = [];

    private RegionManager $regionManager;

    public function __construct(RegionManager $regionManager) {
        $this->regionManager = $regionManager;
    }

    public function onItemUse(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $region = $this->regionManager->getRegionAt($player->getPosition());

        if ($item->getNamedTag()->getInt("bank_note", 0) !== 0) {
            $event->cancel();
            $value = $item->getNamedTag()->getInt("bank_note");
            $player->getWorld()->addSound($player->getLocation(), new XpCollectSound());
            Loader::getPlayerManager()->getSession($player)->addBalance($value);
            $item->pop();
            $player->getInventory()->setItemInHand($item);
        } elseif ($item->getNamedTag()->getInt("experience_bottle", 0) !== 0) {
            $event->cancel();
            $value = $item->getNamedTag()->getInt("experience_bottle");
            $player->sendMessage(C::colorize("&r&l&a+ &r&a" . number_format($value) . " EXP"));
            $player->getWorld()->addSound($player->getLocation(), new XpCollectSound());
            Loader::getPlayerManager()->getSession($player)->addExp($value);
            $item->pop();
            $player->getInventory()->setItemInHand($item);
        } elseif (($bossTag = $item->getNamedTag()->getTag("boss")) !== null) {
            $bossValue = $bossTag->getValue();
            if ($region->getName() === "Warzone") {
                if ($bossValue === "broodmother") {
                    $event->cancel();
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $broodMother = new BroodMother($player->getLocation());
                    $broodMother->spawnToAll();
                } elseif ($bossValue === "ancientguardian") {
                    $event->cancel();
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $ancientGuardian = new AncientGuardian($player->getLocation());
                    $ancientGuardian->spawnToAll();
                } elseif ($bossValue === "blazefury") {
                    $event->cancel();
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $blazeFury = new BlazeFury($player->getLocation());
                    $blazeFury->spawnToAll();
                }
            } else {
                $event->cancel();
                $player->sendMessage(C::colorize("&r&l&cHey! &r&fYou can't do that here!"));
            }

        } elseif (($randomBookTag = $item->getNamedTag()->getTag("random_book")) !== null) {
            $bookValue = $randomBookTag->getValue();
            if ($bookValue === "simple") {
                $event->cancel();
                
                $simpleEnchantments = CustomEnchantments::getAllForRarity(RarityType::SIMPLE());
                
                $randomEnchantment = $simpleEnchantments[array_rand($simpleEnchantments)];
                
                $enchantment = EnchantmentIdMap::getInstance()->fromId($randomEnchantment);

                if ($enchantment instanceof CustomEnchantment) {
                    $level = mt_rand(1, $enchantment->getMaxLevel());
                
                    $successRate = mt_rand(1, 100);
                    $destroyRate = mt_rand(1, 100); 
                    $enchantmentBook = Items::createEnchantmentBook($enchantment, $level, $successRate, $destroyRate);
                
                    $player->getInventory()->addItem($enchantmentBook);
                    
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    
                    $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou examine the &l&fSimple Enchantment Book..."));
                    $player->sendMessage(C::colorize("&r&eand discover &f" . $enchantment->getName() . " " . Utils::getRomanNumeral($level) . "!"));
                }
            } elseif ($bookValue === "unique") {
                $event->cancel();
                
                $uniqueEnchantments = CustomEnchantments::getAllForRarity(RarityType::UNIQUE());
                
                $randomEnchantment = $uniqueEnchantments[array_rand($uniqueEnchantments)];
                
                $enchantment = EnchantmentIdMap::getInstance()->fromId($randomEnchantment);

                if ($enchantment instanceof CustomEnchantment) {
                    $level = mt_rand(1, $enchantment->getMaxLevel());
                
                    $successRate = mt_rand(1, 100);
                    $destroyRate = mt_rand(1, 100); 
                    $enchantmentBook = Items::createEnchantmentBook($enchantment, $level, $successRate, $destroyRate);
                
                    $player->getInventory()->addItem($enchantmentBook);
                    
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    
                    $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou examine the &l&aUnique Enchantment Book..."));
                    $player->sendMessage(C::colorize("&r&eand discover &a" . $enchantment->getName() . " " . Utils::getRomanNumeral($level) . "!"));
                }
            } elseif ($bookValue === "elite") {
                $event->cancel();
                
                $eliteEnchantments = CustomEnchantments::getAllForRarity(RarityType::ELITE());
                
                $randomEnchantment = $eliteEnchantments[array_rand($eliteEnchantments)];
                
                $enchantment = EnchantmentIdMap::getInstance()->fromId($randomEnchantment);

                if ($enchantment instanceof CustomEnchantment) {
                    $level = mt_rand(1, $enchantment->getMaxLevel());
                    
                
                    $successRate = mt_rand(1, 100);
                    $destroyRate = mt_rand(1, 100); 
                    $enchantmentBook = Items::createEnchantmentBook($enchantment, $level, $successRate, $destroyRate);
                
                    $player->getInventory()->addItem($enchantmentBook);
                    
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    
                    $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou examine the &l&bElite Enchantment Book..."));
                    $player->sendMessage(C::colorize("&r&eand discover &b" . $enchantment->getName() . " " . Utils::getRomanNumeral($level) . "!"));
                }
            } elseif ($bookValue === "ultimate") {
                $event->cancel();
                
                $ultimateEnchantments = CustomEnchantments::getAllForRarity(RarityType::ULTIMATE());
                
                $randomEnchantment = $ultimateEnchantments[array_rand($ultimateEnchantments)];
                
                $enchantment = EnchantmentIdMap::getInstance()->fromId($randomEnchantment);

                if ($enchantment instanceof CustomEnchantment) {
                    $level = mt_rand(1, $enchantment->getMaxLevel());
                    
                
                    $successRate = mt_rand(1, 100);
                    $destroyRate = mt_rand(1, 100); 
                    $enchantmentBook = Items::createEnchantmentBook($enchantment, $level, $successRate, $destroyRate);
                
                    $player->getInventory()->addItem($enchantmentBook);
                    
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    
                    $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou examine the &l&eUltimate Enchantment Book..."));
                    $player->sendMessage(C::colorize("&r&eand discover &e" . $enchantment->getName() . " " . Utils::getRomanNumeral($level) . "!"));
                }
            } elseif ($bookValue === "legendary") {
                $event->cancel();
                
                $legendaryEnchantments = CustomEnchantments::getAllForRarity(RarityType::LEGENDARY());
                
                $randomEnchantment = $legendaryEnchantments[array_rand($legendaryEnchantments)];
                
                $enchantment = EnchantmentIdMap::getInstance()->fromId($randomEnchantment);

                if ($enchantment instanceof CustomEnchantment) {
                    $level = mt_rand(1, $enchantment->getMaxLevel());
                    
                
                    $successRate = mt_rand(1, 100);
                    $destroyRate = mt_rand(1, 100); 
                    $enchantmentBook = Items::createEnchantmentBook($enchantment, $level, $successRate, $destroyRate);
                
                    $player->getInventory()->addItem($enchantmentBook);
                    
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    
                    $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou examine the &l&6Legendary Enchantment Book..."));
                    $player->sendMessage(C::colorize("&r&eand discover &6" . $enchantment->getName() . " " . Utils::getRomanNumeral($level) . "!"));
                }
            } elseif ($bookValue === "heroic") {
                $event->cancel();
                
                $heroicEnchantments = CustomEnchantments::getAllForRarity(RarityType::HEROIC());
                
                $randomEnchantment = $heroicEnchantments[array_rand($heroicEnchantments)];
                
                $enchantment = EnchantmentIdMap::getInstance()->fromId($randomEnchantment);

                if ($enchantment instanceof CustomEnchantment) {
                    $level = mt_rand(1, $enchantment->getMaxLevel());
                    
                
                    $successRate = mt_rand(1, 100);
                    $destroyRate = mt_rand(1, 100); 
                    $enchantmentBook = Items::createEnchantmentBook($enchantment, $level, $successRate, $destroyRate);
                
                    $player->getInventory()->addItem($enchantmentBook);
                    
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    
                    $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou examine the &l&dHeroic Enchantment Book..."));
                    $player->sendMessage(C::colorize("&r&eand discover &d" . $enchantment->getName() . " " . Utils::getRomanNumeral($level) . "!"));
                }
            } elseif ($bookValue === "generator") {
                $event->cancel();
                
                $rarities = [
                    "simple", 
                    "unique", 
                    "elite", 
                    "ultimate", 
                    "legendary"
                ];
            
                $randomRarity = $rarities[array_rand($rarities)];

                if ($randomRarity === "simple") {
                    if ($player->getInventory()->canAddItem(Items::createRandomCEBook("simple"))) {
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou found 16x &r&l&fSimple Enchantment Books&r&e!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $player->getInventory()->addItem(Items::createRandomCEBook("simple", 16));
                    } else {
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou don't have enough space in your inventory"));
                    }
                } elseif ($randomRarity === "unique") {
                    if ($player->getInventory()->canAddItem(Items::createRandomCEBook("unique"))) {
                        $item->pop();
                        $player->getInventory()->setItemInHand($item); 
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou found 8x &r&l&aUnique Enchantment Books&r&e!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $player->getInventory()->addItem(Items::createRandomCEBook("unique", 8));
                    } else {
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou don't have enough space in your inventory"));
                    }
                } elseif ($randomRarity === "elite") {
                    if ($player->getInventory()->canAddItem(Items::createRandomCEBook("elite"))) {
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou found 4x &r&l&bElite Enchantment Books&r&e!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $player->getInventory()->addItem(Items::createRandomCEBook("elite", 4));
                    } else {
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou don't have enough space in your inventory"));
                    }
                } elseif ($randomRarity === "ultimate") {
                    if ($player->getInventory()->canAddItem(Items::createRandomCEBook("ultimate"))) {
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou found 2x &r&l&eUltimate Enchantment Books&r&e!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $player->getInventory()->addItem(Items::createRandomCEBook("ultimate", 2));
                    } else {
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou don't have enough space in your inventory"));
                    }
                } elseif ($randomRarity === "legendary") {
                    if ($player->getInventory()->canAddItem(Items::createRandomCEBook("legendary"))) {
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou found 1x &r&l&6Legendary Enchantment Books&r&e!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $player->getInventory()->addItem(Items::createRandomCEBook("legendary", 1)); 
                    } else {
                        $player->sendMessage(C::colorize("&r&e&l(!) &r&eYou don't have enough space in your inventory"));
                    }
                }
            }
        } elseif ($item->getNamedTag()->getTag("enchant_book") !== null) {
            $player->sendMessage(C::colorize("&r&e&l(!) &r&eTo apply this enchantment to an item, simply drag n' drop the"));
            $player->sendMessage(C::colorize("&r&e book onto the item you'd like to enchant in your inventory!"));
            $player->sendMessage(C::colorize("&r&7The &bSucess Rate &7is the chance of the book successfully being"));
            $player->sendMessage(C::colorize("&r&7applied to your equipment. The &bDestroy Rate &7is the"));
            $player->sendMessage(C::colorize("&r&7percent chance of your piece of equipment being DESTROYED if"));
            $player->sendMessage(C::colorize("&r&7the book fails to apply."));
        } elseif (($enchantscroll = $item->getNamedTag()->getTag("scrolls")) !== null) {
            $value = $enchantscroll->getValue();
            if ($value === "lorecrystal") {
                if (isset($this->lorerenamer[$player->getName()])) {
                    $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a lore rename tag type cancel to remove it!");
                    return;
                }
                if (isset($this->itemRenamer[$player->getName()])) {
                    $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a item tag type cancel to remove it!");
                    return;
                }
                $this->lorerenamer[$player->getName()] = $player;
                $player->sendMessage("    §r§6§lLore Rename Usage");
                $player->sendMessage("§r§61. §r§7Hold the ITEM you'd like to edit.");
                $player->sendMessage("§r§62. §r§7Send the new name as a chat message §lwith & color codes§r§7.");
                $player->sendMessage("§r§63. §r§7Confirm the preview of the new name that is displayed.");
                Utils::playSound($player, "mob.enderdragon.flap", 2);
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                return;
            } elseif ($value === "itemrename") {
                if (isset($this->itemRenamer[$player->getName()])) {
                    $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a item tag type cancel to remove it!");
                    return;
                }
                if (isset($this->lorerenamer[$player->getName()])) {
                    $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a lore rename tag type cancel to remove it!");
                    return;
                }
                $this->itemRenamer[$player->getName()] = $player;
                $player->sendMessage("    §r§6§lRename-Tag Usage");
                $player->sendMessage("§r§61. §r§7Hold the ITEM you'd like to rename.");
                $player->sendMessage("§r§62. §r§7Send the new name as a chat message §lwith & color codes§r§7.");
                $player->sendMessage("§r§63. §r§7Confirm the preview of the new name that is displayed.");
                Utils::playSound($player, "mob.enderdragon.flap", 2);
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                return;
            }
        } elseif (($rank_voucher = $item->getNamedTag()->getTag("rank_voucher")) !== null) {
            $value = $rank_voucher->getValue();
            if ($value === "seeker") {
                $session = RankSystem::getInstance()->getSessionManager()->get($player);
                
                if ($session !== null) {
                    $restrictedRanks = ["Seeker", "Luminary", "Celestial", "Elysian", "Ascendant", "Owner"];
                    $highestRank = $session->getHighestRank()->getName();
                    
                    if (in_array($highestRank, $restrictedRanks)) {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou are already Seeker rank or above!"));
                    } else {
                        $session->setRank(RankSystem::getInstance()->getRankManager()->getRank(ucfirst($value)));
                        $player->sendMessage(C::colorize("&r&l&5    (!) &r&d&lRank Upgrade &5(!)"));
                        $player->sendMessage(C::colorize("&r&8» &r&7Your rank has been upgraded to " . ucfirst($value) . "!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                    }
                }
            } elseif ($value === "luminary") {
                $session = RankSystem::getInstance()->getSessionManager()->get($player);
            
                if ($session !== null) {
                    $ranks = ["Luminary", "Celestial", "Elysian", "Ascendant", "Owner"];
                    $highestRank = $session->getHighestRank()->getName();
            
                    if (array_search($highestRank, $ranks) !== false && array_search($highestRank, $ranks) <= array_search(ucfirst($value), $ranks)) {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou are already Luminary rank or above!"));
                    } else {
                        $session->setRank(RankSystem::getInstance()->getRankManager()->getRank(ucfirst($value)));
                        $player->sendMessage(C::colorize("&r&l&5    (!) &r&d&lRank Upgrade &5(!)"));
                        $player->sendMessage(C::colorize("&r&8» &r&7Your rank has been upgraded to " . ucfirst($value) . "!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                    }
                }
            } elseif ($value === "celestial") {
                $session = RankSystem::getInstance()->getSessionManager()->get($player);
            
                if ($session !== null) {
                    $ranks = ["Celestial", "Elysian", "Ascendant", "Owner"];
                    $highestRank = $session->getHighestRank()->getName();
            
                    if (array_search($highestRank, $ranks) !== false && array_search($highestRank, $ranks) <= array_search(ucfirst($value), $ranks)) {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou are already " . ucfirst($value) . " rank or above!"));
                    } else {
                        $session->setRank(RankSystem::getInstance()->getRankManager()->getRank(ucfirst($value)));
                        $player->sendMessage(C::colorize("&r&l&5    (!) &r&d&lRank Upgrade &5(!)"));
                        $player->sendMessage(C::colorize("&r&8» &r&7Your rank has been upgraded to " . ucfirst($value) . "!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                    }
                }
            } elseif ($value === "elysian") {
                $session = RankSystem::getInstance()->getSessionManager()->get($player);
            
                if ($session !== null) {
                    $ranks = ["Elysian", "Ascendant", "Owner"];
                    $highestRank = $session->getHighestRank()->getName();
            
                    if (array_search($highestRank, $ranks) !== false && array_search($highestRank, $ranks) <= array_search(ucfirst($value), $ranks)) {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou are already " . ucfirst($value) . " rank or above!"));
                    } else {
                        $session->setRank(RankSystem::getInstance()->getRankManager()->getRank(ucfirst($value)));
                        $player->sendMessage(C::colorize("&r&l&5    (!) &r&d&lRank Upgrade &5(!)"));
                        $player->sendMessage(C::colorize("&r&8» &r&7Your rank has been upgraded to " . ucfirst($value) . "!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                    }
                }
            } elseif ($value === "Ascendant") {
                $session = RankSystem::getInstance()->getSessionManager()->get($player);
            
                if ($session !== null) {
                    $ranks = ["Ascendant", "Owner"];
                    $highestRank = $session->getHighestRank()->getName();
            
                    if (array_search($highestRank, $ranks) !== false && array_search($highestRank, $ranks) <= array_search(ucfirst($value), $ranks)) {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou are already " . ucfirst($value) . " rank or above!"));
                    } else {
                        $session->setRank(RankSystem::getInstance()->getRankManager()->getRank(ucfirst($value)));
                        $player->sendMessage(C::colorize("&r&l&5    (!) &r&d&lRank Upgrade &5(!)"));
                        $player->sendMessage(C::colorize("&r&8» &r&7Your rank has been upgraded to " . ucfirst($value) . "!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                    }
                }
            }
        } elseif (($title_voucher = $item->getNamedTag()->getTag("title_voucher")) !== null) {
            $value = $title_voucher->getValue();
            $session = RankSystem::getInstance()->getSessionManager()->get($player);

            if ($session !== null) {
                if ($value === "Stormcaller") {
                    if (!$player->hasPermission("title.stormcaller")) {
                        $session->setPermission("title.stormcaller");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Stormcaller title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Stormcaller title!"));
                    }
                } elseif ($value === "Vlone") {
                    if (!$player->hasPermission("title.vlone")) {
                        $session->setPermission("title.vlone");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Vlone title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Vlone title!"));
                    }
                } elseif ($value === "GBGR") {
                    if (!$player->hasPermission("title.gbgr")) {
                        $session->setPermission("title.gbgr");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the GBGR title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the GBGR title!"));
                    }
                } elseif ($value === "Euphoria") {
                    if (!$player->hasPermission("title.euphoria")) {
                        $session->setPermission("title.euphoria");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Euphoria title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Euphoria title!"));
                    }
                } elseif ($value === "Elysium") {
                    if (!$player->hasPermission("title.elysium")) {
                        $session->setPermission("title.elysium");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Elysium title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {    
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Elysium title!"));
                    }
                } elseif ($value === "UrTrash") {
                    if (!$player->hasPermission("title.urtrash")) {
                        $session->setPermission("title.urtrash");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the UrTrash title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the UrTrash title!"));
                    }
                } elseif ($value === "$$$") {
                    if (!$player->hasPermission("title.$$$")) {
                        $session->setPermission("title.$$$");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the $$$ title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the $$$ title!"));
                    }
                } elseif ($value === "P2W") {
                    if (!$player->hasPermission("title.p2w")) {
                        $session->setPermission("title.p2w");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the P2W title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the P2W title!"));
                    }
                } elseif ($value === "Panda") {
                    if (!$player->hasPermission("title.panda")) {
                        $session->setPermission("title.panda");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Panda title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Panda title!"));  
                    }
                } elseif ($value === "OP") {
                    if (!$player->hasPermission("title.OP")) {
                        $session->setPermission("title.op");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the OP title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the OP title!"));
                    }
                } elseif ($value === "God") {
                    if (!$player->hasPermission("title.god")) {
                        $session->setPermission("title.god");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the God title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the God title!"));
                    }
                } elseif ($value === "Soulmaster") {
                    if (!$player->hasPermission("title.soulmaster")) {
                        $session->setPermission("title.soulmaster");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Soulmaster title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Soulmaster title!"));
                    }
                } elseif ($value === "P2L") {
                    if (!$player->hasPermission("title.p2l")) {
                        $session->setPermission("title.p2l");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the P2L title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the P2L title!"));
                    }
                } elseif ($value === "k") {
                    if (!$player->hasPermission("title.k")) {
                        $session->setPermission("title.k");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the k title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the k title!"));
                    }
                } elseif ($value === "CartFein") {
                    if (!$player->hasPermission("title.cartfein")) {
                        $session->setPermission("title.cartfein");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the CartFein title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the CartFein title!"));
                    }
                } elseif ($value === "Blinker") {
                    if (!$player->hasPermission("title.blinker")) {
                        $session->setPermission("title.blinker");
                        $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Blinker title!"));
                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                        $item->pop();
                        $player->getInventory()->setItemInHand($item);
                    } else {
                        $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Blinker title!"));
                }
            }
        }
    } elseif (($perk_voucher = $item->getNamedTag()->getTag("perk_voucher")) !== null) {
        $perk = $perk_voucher->getValue();
        $session = RankSystem::getInstance()->getSessionManager()->get($player);

        if ($perk === "randomizer") {
            $perks = ["fixall", "heal", "fly", "near", "bless"];
            $p = $perks[array_rand($perks)];

            $player->getInventory()->addItem(Items::createPerkVoucher($p, 1));
            $player->sendMessage(C::colorize("&r&l&a(!) &r&aYour perk voucher has been given."));
            $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
            $item->pop();
            $player->getInventory()->setItemInHand($item);
        } elseif ($perk === "fixall") {
            if (!$player->hasPermission("command.fixall")) {
                $session->setPermission("command.fixall");
                $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Fix All Perk."));
                $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                $item->pop();
                $player->getInventory()->setItemInHand($item);
            } else {
                $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Fix All Perk."));
            }
        } elseif ($perk === "heal") {
            if (!$player->hasPermission("command.heal")) {
                $session->setPermission("command.heal");
                $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Heal Perk."));
                $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                $item->pop();
                $player->getInventory()->setItemInHand($item);
            } else {
                $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Heal Perk."));
            }
        } elseif ($perk === "fly") {
            if (!$player->hasPermission("command.fly")) {
                $session->setPermission("command.fly");
                $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Fly Perk."));
                $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                $item->pop();
                $player->getInventory()->setItemInHand($item);
            } else {
                $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Fly Perk."));
            }
        } elseif ($perk === "near") {
            if (!$player->hasPermission("command.near")) {
                $session->setPermission("command.near");
                $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Near Perk."));
                $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                $item->pop();
                $player->getInventory()->setItemInHand($item);
            } else {
                $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Near Perk."));
            }
            } elseif ($perk === "bless") {
                if (!$player->hasPermission("command.bless")) {
                    $session->setPermission("command.bless");
                    $player->sendMessage(C::colorize("&r&l&a(!) &r&aYou have unlocked the Bless Perk."));
                    $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                } else {
                    $player->sendMessage(C::colorize("&r&l&c(!) &r&cYou have already unlocked the Bless Perk."));
                }
            }
        } elseif (($citems = $item->getNamedTag()->getTag("max_home")) !== null) {
            $citem = $citems->getValue();
            $homeSession = Loader::getHomeManager();
            
            $item->pop(); 
            $player->getInventory()->setItemInHand($item);
            $player->sendMessage(C::colorize("&r&l&a(!) &r&aYour max homes has increased by $citem."));
            $homeSession->setMaxHomes($player->getUniqueId(), $citem);
        }
    }

    public function useLootbox(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $tag = $item->getNamedTag();

        if (($lbtag = $tag->getTag("lootbox")) !== null) {
            $lootbox = $lbtag->getValue();
            $event->cancel();
        
            ElysiumUtils::openLootbox($player, $lootbox);
            $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
            $item->pop();
            $player->getInventory()->setItemInHand($item);

        }
    
    }
    
    public function useDropPackage(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $tag = $item->getNamedTag();

        if (($dptag = $tag->getTag("drop_package")) !== null) {
            $dropPackage = $dptag->getValue();
            $event->cancel();
        
            DropPackages::openDropPackage($player, $dropPackage)->send($player);
            $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
            $item->pop();
            $player->getInventory()->setItemInHand($item);
        }
    }

    public function useIncursionSummoner(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $tag = $item->getNamedTag();

        if (($incursionTag = $tag->getTag("incursion")) !== null) {
            $incursion = $incursionTag->getValue();
            $event->cancel();

            IncursionManager::spawnIncursion($incursion, function ($position) use ($player, $item, $incursion) {
                switch ($incursion) {
                    case "astral":
                        $player->sendMessage(C::colorize("&r&l&e(!) &r&eYou have forced an Astral Incursion!"));
                        break;
                    case "soul":
                        $player->sendMessage(C::colorize("&r&l&e(!) &r&eYou have forced a Soul Incursion!"));
                        break;
                    case "hollow":
                        $player->sendMessage(C::colorize("&r&l&e(!) &r&eYou have forced a Hollow Incursion using Hollow Bait!"));
                        break;
                }

                $item->pop();
                $player->getInventory()->setItemInHand($item);
            });
        }
    }

    public function onDropScroll(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $items = [ItemTypeIds::DIAMOND_HELMET, ItemTypeIds::DIAMOND_CHESTPLATE, ItemTypeIds::DIAMOND_LEGGINGS, ItemTypeIds::DIAMOND_BOOTS, ItemTypeIds::DIAMOND_SWORD, ItemTypeIds::DIAMOND_SHOVEL, ItemTypeIds::DIAMOND_PICKAXE, ItemTypeIds::DIAMOND_AXE, ItemTypeIds::DIAMOND_HOE];
                if ($action instanceof SlotChangeAction
                    && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction
                    && (
                        ($itemClickedWith = $action->getTargetItem())->getTypeId() === VanillaItems::INK_SAC()->getTypeId() || 
                        ($itemClickedWith->getTypeId() === VanillaItems::PAPER()->getTypeId() ||
                        $itemClickedWith->getTypeId() === StringToItemParser::getInstance()->parse("empty_map")->getTypeId() || 
                        $itemClickedWith->getTypeId() === VanillaItems::MAGMA_CREAM()->getTypeId())
                    )
                    && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId()
                    && in_array($itemClicked->getTypeId(), $items)
                    && $itemClickedWith->getCount() === 1
                    && $itemClickedWith->getNamedTag()->getTag("scrolls")
                ) {
                    $scrollType = $itemClickedWith->getNamedTag()->getString("scrolls");
                    $event->cancel();

                    if ($scrollType === "blackscroll") {
                        $enchantments = $itemClicked->getEnchantments();

                        if (!empty($enchantments)) {
                            $randomKey = array_rand($enchantments);
                            $removedEnchantment = $enchantments[$randomKey];
    
                            $itemClicked->removeEnchantment($removedEnchantment->getType());
    
                            $lore = $itemClicked->getLore();
                            $enchantmentName = $removedEnchantment->getType()->getName();
                            $rarity = $removedEnchantment->getType()->getRarity();
                            $loreLine = EnchantUtils::translateRarityToColor($rarity) . $enchantmentName;
                            $loreLineIndex = array_search($loreLine, $lore);
                            if ($loreLineIndex !== false) {
                                unset($lore[$loreLineIndex]);
                                $itemClicked->setLore($lore);
                            }

                            $action->getInventory()->addItem(Items::createEnchantmentBook($removedEnchantment->getType(), $removedEnchantment->getLevel(), $itemClickedWith->getNamedTag()->getInt("black_scroll"), rand(1, 100)));
                        }
    
                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                        return;
                    } elseif ($scrollType === "transmog") {
                        $enchantments = $itemClicked->getEnchantments();
                        $enchantments = CustomEnchantments::sortEnchantmentsByRarity($enchantments);
                        $itemName = $itemClicked->getName();
                        
                        if (preg_match('/ §r§l§8\[§r§f\d+§l§8\]§r/', $itemName)) {
                            $itemName = preg_replace('/ §r§l§8\[§r§f\d+§l§8\]§r/', '', $itemName);
                        }
                        
                        $enchantmentCount = count($enchantments);
                        $itemName .= " §r§l§8[§r§f{$enchantmentCount}§l§8]§r";
                        $itemClicked->setCustomName($itemName);
                        
                        foreach ($enchantments as $enchantmentInstance) {
                            $itemClicked->removeEnchantment($enchantmentInstance->getType());
                        }
                        
                        foreach ($enchantments as $enchantmentInstance) {
                            $itemClicked->addEnchantment($enchantmentInstance);
                        }
                        
                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                        return;
                    } elseif ($scrollType === "whitescroll") {
                        $itemClicked->getNamedTag()->setString("protected", "true");
                        $lore = "§r§l§fPROTECTED";
                        $itemClicked->setLore([$lore]);

                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                        return;
                    } elseif ($scrollType === "killcounter") {
                        if ($itemClicked->getNamedTag()->getTag("killcounter")) {
                            $transaction->getSource()->sendMessage(C::colorize("&r&l&c(!) &r&cThis item already has a player kill counter!"));
                            $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                            return;
                        }

                        $event->cancel();
                        $lore = "§r§ePlayer Kills: §60";
                        $itemClicked->setLore([$lore]);
                        $itemClicked->getNamedTag()->setString("scrolls", "killcounter");
                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    }
                }
            }
        }
    }

    public function onDropEnchantFragment(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $items = [ItemTypeIds::DIAMOND_HELMET, ItemTypeIds::DIAMOND_CHESTPLATE, ItemTypeIds::DIAMOND_LEGGINGS, ItemTypeIds::DIAMOND_BOOTS, ItemTypeIds::DIAMOND_SWORD, ItemTypeIds::DIAMOND_SHOVEL, ItemTypeIds::DIAMOND_PICKAXE, ItemTypeIds::DIAMOND_AXE, ItemTypeIds::DIAMOND_HOE];
                if ($action instanceof SlotChangeAction
                    && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction
                    && (
                        ($itemClickedWith = $action->getTargetItem())->getTypeId() === VanillaItems::LAPIS_LAZULI()->getTypeId() || 
                        ($itemClickedWith->getTypeId() === VanillaItems::IRON_INGOT()->getTypeId() ||
                        $itemClickedWith->getTypeId() === VanillaItems::REDSTONE_DUST()->getTypeId() || VanillaItems::GOLD_INGOT()->getTypeId())
                    )
                    && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId()
                    && in_array($itemClicked->getTypeId(), $items)
                    && $itemClickedWith->getCount() === 1
                    && $itemClickedWith->getNamedTag()->getTag("enchantmentfragment")
                ) {
                    $scrollType = $itemClickedWith->getNamedTag()->getString("enchantmentfragment");
                    $event->cancel();

                    if ($scrollType === "unbreakingv") {
                        if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "unbreakingv") {
                            $itemClicked->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5));
                            $itemClicked->getNamedTag()->setString("enchantmentfragment", "unbreakingv");

                            $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                            $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                            $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                            return;
                        }

                    } elseif ($scrollType === "thornsiii" && $itemClicked instanceof Armor) {
                        if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "thornsiii") {
                            $itemClicked->addEnchantment(new EnchantmentInstance(VanillaEnchantments::THORNS(), 3));
                            $itemClicked->getNamedTag()->setString("enchantmentfragment", "thornsiii");
                            
                            $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                            $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                            $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                            return;
                            
                        }
                        
                } elseif ($scrollType === "depthstrideriii" && $itemClicked->getTypeId() === ItemTypeIds::DIAMOND_BOOTS) {
                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "depthstrideriii") {
                        $itemClicked->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("depth_strider"), 3));
                        $itemClicked->getNamedTag()->setString("enchantmentfragment", "depthstrideriii");
                        
                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                        return;
                        
                    }
                } elseif ($scrollType === "lootingv" && ($itemClicked instanceof Sword || $itemClicked instanceof Axe)) {
                    if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "fortunev") {
                        $itemClicked->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("looting"), 5));
                        $itemClicked->getNamedTag()->setString("enchantmentfragment", "lootingv");
                        
                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                        return;
                
                    }
                    } elseif ($scrollType === "fortunev" && $itemClicked instanceof Pickaxe) {
                        if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "fortunev") {
                            $itemClicked->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 5));
                            $itemClicked->getNamedTag()->setString("enchantmentfragment", "fortunev");
                            
                            $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                            $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                            $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                            return;
                    
                        }
                    } elseif ($scrollType === "fireaspectiii" && $itemClicked instanceof Sword) {
                        if ($itemClicked->getNamedTag()->getString("enchantmentfragment", "") !== "fireaspectiii") {
                            $itemClicked->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT(), 3));
                            $itemClicked->getNamedTag()->setString("enchantmentfragment", "fireaspectiii");
                            
                            $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                            $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                            $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                            return;
                        }
                    }
                }
            }
        }
    }
    
    public function onDropEnchantBook(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $items = [
                    ItemTypeIds::DIAMOND_HELMET,
                    ItemTypeIds::DIAMOND_CHESTPLATE,
                    ItemTypeIds::DIAMOND_LEGGINGS,
                    ItemTypeIds::DIAMOND_BOOTS,
                    ItemTypeIds::DIAMOND_SWORD,
                    ItemTypeIds::DIAMOND_SHOVEL,
                    ItemTypeIds::DIAMOND_PICKAXE,
                    ItemTypeIds::DIAMOND_AXE,
                    ItemTypeIds::DIAMOND_HOE
                ];
                if ($action instanceof SlotChangeAction
                    && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction
                    && ($itemClickedWith = $action->getTargetItem())->getTypeId() === VanillaItems::ENCHANTED_BOOK()->getTypeId()
                    && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId()
                    && in_array($itemClicked->getTypeId(), $items)
                    && $itemClickedWith->getCount() === 1
                    && $itemClickedWith->getNamedTag()->getTag("enchant_book")
                ) {
                    $scrollType = $itemClickedWith->getNamedTag()->getString("enchant_book");
                    $event->cancel();
                    $enchantment = StringToEnchantmentParser::getInstance()->parse($scrollType);

                        if ($enchantment instanceof CustomEnchantment) {
                            $customEnchantmentsCount = 0;
                            foreach ($itemClicked->getEnchantments() as $enchantmentInstance) {
                                if ($enchantmentInstance->getType() instanceof CustomEnchantment) {
                                    $customEnchantmentsCount++;
                                }
                            }
            
                            if ($customEnchantmentsCount >= 7) {
                                $transaction->getSource()->sendMessage(C::colorize("&r&l&c(!) &r&cThis item already has 7 custom enchantments!"));
                                $event->cancel();
                                return;
                            }

                            if ($scrollType === strtolower($enchantment->getName())) {
                                $applicable = CustomEnchantment::getApplicable($enchantment);
                                if ($applicable) {
                                    if (CustomEnchantment::matchesApplicable($itemClicked, $applicable)) {
                                        if (($successRate = $itemClickedWith->getNamedTag()->getInt("successrate")) !== 0 &&
                                            ($destroyRate = $itemClickedWith->getNamedTag()->getInt("destroyrate")) !== 0 &&
                                            ($level = $itemClickedWith->getNamedTag()->getInt("level")) !== 0) {
                                            $existingEnchantment = $itemClicked->getEnchantment($enchantment);
                                            if (!$existingEnchantment || $existingEnchantment->getLevel() < $level) {
                                                if (mt_rand(1, 100) <= $successRate) {
                                                    $itemClicked->addEnchantment(new EnchantmentInstance($enchantment, $level));
                                                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);

                                                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                                                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                                                    return;
                                                    
                                                } else {
                                                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                                            
                                                    if (mt_rand(1, 100) <= $destroyRate) {
                                                        if (Utils::hasTag($itemClicked, "protected", "true")) {
                                                            $transaction->getSource()->sendToastNotification(C::colorize("&r&l&e(!) &r&eYour Item was protected by the Whitescroll"), C::colorize("&r&7The Whitescroll protected your item from being destroyed by the enchantment book"));
                                                        
                                                            $itemClicked->getNamedTag()->removeTag("protected");
                                                            $lore = $itemClicked->getLore();
                                                            if (isset($lore[array_search("§r§l§fPROTECTED", $lore)])) {
                                                            unset($lore[array_search("§r§l§fPROTECTED", $lore)]);
                                                        }
                                                        $itemClicked->setLore($lore);
                                                        $transaction->getSource()->getInventory()->setItem($action->getSlot(), $itemClicked);
                                                    } else {

                                                        $action->getInventory()->setItem($action->getSlot(), VanillaItems::AIR());
                                                        return;
                                                    }
                                                }
                                            }
                                        } else {
                                            $transaction->getSource()->sendToastNotification(C::colorize("&r&l&c(!) &r&cThis item already has " . ucfirst($enchantment->getName()) . "!"), C::colorize("&r&7The enchantment already exists on the item at the same level or higher."));
                                        }
                                    }  
                                }
                            }
                        }
                    }
                }  
            }   
        }
    }

    /**             ITEM RENAME          */
    public function onItemRename(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $itemRenamer = $this->itemRenamer[$player->getName()] ?? null;
        if ($itemRenamer === null) {
            return;
        }
    
        $message = $event->getMessage();
        $hand = $player->getInventory()->getItemInHand();
        $event->cancel();
    
        switch ($message) {
            case "cancel":
                $this->handleCancel($player);
                break;
            case "confirm":
                $this->handleConfirm($player, $hand);
                break;
            default:
                $this->handleNaming($player, $message);
                break;
        }
    }

    private function handleCancel(Player $player): void
    {
        $player->sendMessage("§r§c§l** §r§cYou have unqueued your Itemtag for this usage.");
        Utils::playSound($player, "mob.enderdragon.flap", 2);
        $player->getInventory()->addItem(Items::getEnchantScrolls("itemrename", 1));
        unset($this->itemRenamer[$player->getName()]);
        unset($this->message[$player->getName()]);
    }

    private function handleConfirm(Player $player, Item $hand): void
    {
        if (!isset($this->message[$player->getName()])) {
            return;
        }

        $this->sendMessageAndSound($player, "§r§e§l(!) §r§eYour ITEM has been renamed to: '{$this->message[$player->getName()]}§e'");
        $hand->setCustomName($this->message[$player->getName()]);
        $player->getInventory()->setItemInHand($hand);
        unset($this->itemRenamer[$player->getName()]);
        unset($this->message[$player->getName()]);
    }   

    private function handleNaming(Player $player, string $message): void
    {
        if (strlen($message) > 26) {
            $player->sendMessage("§r§cYour custom name exceeds the 36 character limit.");
            return;
        }

        $formatted = C::colorize($message);
        $this->sendMessageAndSound($player, "§r§e§l(!) §r§eItem Name Preview: $formatted");
        $player->sendMessage("§r§7Type '§r§aconfirm§7' if this looks correct, otherwise type '§ccancel§7' to start over.");
        $this->message[$player->getName()] = $formatted;
    }

    private function sendMessageAndSound(Player $player, string $message): void
    {
        $player->sendMessage($message);
        $player->getLocation()->getWorld()->addSound($player->getLocation(), new XpLevelUpSound(100));
    }
 
    /**              LORE RENAME                 */
    public function onLoreRename(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $lorerenamer = $this->lorerenamer[$player->getName()] ?? null;
        if ($lorerenamer === null) {
            return;
        }
    
        $message = $event->getMessage();
        $hand = $player->getInventory()->getItemInHand();
        $event->cancel();
    
        switch ($message) {
            case "cancel":
                $this->handleLoreCancel($player);
                break;
            case "confirm":
                $this->handleLoreConfirm($player, $hand);
                break;
            default:
                $this->handleLoreNaming($player, $message);
                break;
        }
    }
    
    private function handleLoreCancel(Player $player): void
    {
        $player->sendMessage("§r§c§l** §r§cYou have unqueued your Lore-Renamer for this usage.");
        Utils::playSound($player, "mob.enderdragon.flap", 2);
        $player->getInventory()->addItem(Items::getEnchantScrolls("lorecrystal", 1));
        unset($this->lorerenamer[$player->getName()]);
        unset($this->messages[$player->getName()]);
    }
    
    private function handleLoreConfirm(Player $player, Item $hand): void
    {
        if (!isset($this->messages[$player->getName()])) {
            return;
        }
    
        $this->sendMessageAndSound($player, "§r§e§l(!) §r§eYour ITEM's lore has been set to: '{$this->messages[$player->getName()]}§e'");
        $lore = $hand->getLore();
        $lore[] = $this->messages[$player->getName()];
        $hand->setLore($lore);
        $player->getInventory()->setItemInHand($hand);
        unset($this->lorerenamer[$player->getName()]);
        unset($this->messages[$player->getName()]);
    }
    
    private function handleLoreNaming(Player $player, string $message): void
    {
        if (strlen($message) > 18) {
            $player->sendMessage("§r§cYour custom lore exceeds the 18 character limit.");
            return;
        }
    
        $formatted = C::colorize($message);
        $this->sendMessageAndSound($player, "§r§e§l(!) §r§eItem Name Preview: $formatted");
        $player->sendMessage("§r§7Type '§r§aconfirm§7' if this looks correct, otherwise type '§ccancel§7' to start over.");
        $this->messages[$player->getName()] = $formatted;
    }
}