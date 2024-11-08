<?php

namespace xtcy\ElysiumCore\listeners;

use pocketmine\block\Beacon;
use pocketmine\block\EnderChest;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\LuckyPouches\utils\PouchItem;
use xtcy\ElysiumCore\items\Items;
use xtcy\ElysiumCore\utils\Menu\CratePreviews;

class CrateListener implements Listener
{

    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $player->getInventory()->getItemInHand();
        $world = $player->getWorld();
        $voteCrate = new Position(-77, 158, 156, $world);
        $cipherCrate = new Position(-79, 158, 148, $world);
        $zenithCrate = new Position(-79, 158, 164, $world);
        $empyreanCrate = new Position(-87, 157, 156, $world);

        if ($block->getPosition()->floor()->equals($voteCrate) && $block instanceof EnderChest) {
            $event->cancel();
            if (($tag = $item->getNamedTag()->getTag("crate_key")) !== null && $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                $keyValue = $tag->getValue();
                if ($keyValue === "vote") {
                    $voteItems = [
                        ["item" => PouchItem::getPouchType("simple"), "chance" => 20],
                        ["item" => PouchItem::getPouchType("simple_gem"), "chance" => 10],
                        ["item" => PouchItem::getPouchType("simple_xp"), "chance" => 15],
                        ["item" => Items::getCrateKey("vote", 2), "chance" => 20],
                        ["item" => Items::getCrateKey("cipher"), "chance" => 15],
                        ["item" => Items::getCrateKey("zenith"), "chance" => 10],
                        ["item" => Items::getCrateKey("empyrean"), "chance" => 5],
                        ["item" => Items::createBankNote(null, 10000), "chance" => 25],
                        ["item" => Items::createBankNote(null, 25000), "chance" => 20],
                        ["item" => Items::createBankNote(null, 50000), "chance" => 15],
                        ["item" => Items::createExperienceBottle(null, 1000), "chance" => 25],
                        ["item" => Items::createExperienceBottle(null, 2500), "chance" => 20],
                        ["item" => Items::createExperienceBottle(null, 5000), "chance" => 15],
                        ["item" => Items::createEnchantFragment("unbreaking", 1), "chance" => 10],
                        ["item" => Items::createRandomCEBook("simple", 1), "chance" => 15],
                        ["item" => Items::createRandomCEBook("unique", 1), "chance" => 10],
                        ["item" => Items::createRankVoucher("seeker", 1), "chance" => 6],
                        ["item" => Items::createRankVoucher("luminary", 1), "chance" => 5],
                        ["item" => Items::createRankVoucher("celestial", 1), "chance" => 5],
                        ["item" => Items::createRankVoucher("elsysian", 1), "chance" => 4],
                        ["item" => Items::createRankVoucher("ascendant", 1), "chance" => 2],
                    ];

                    $totalChance = array_reduce($voteItems, function ($carry, $item) {
                        return $carry + $item["chance"];
                    }, 0);

                    $randomNumber = mt_rand(0, $totalChance);

                    $chosenItem = null;

                    if ($player->isSneaking()) {
                        $keysInHand = $item->getCount();
                        $keysToOpen = $keysInHand;
        
                        for ($i = 0; $i < $keysInHand; $i++) {
                            $totalChance = array_reduce($voteItems, function ($carry, $item) {
                                return $carry + $item["chance"];
                            }, 0);
        
                            $randomNumber = mt_rand(0, $totalChance);
        
                            $chosenItem = null;
                            foreach ($voteItems as $itemData) {
                                $randomNumber -= $itemData["chance"];
                                if ($randomNumber <= 0) {
                                    $chosenItem = $itemData["item"];
                                    break;
                                }
                            }
        
                            if ($chosenItem instanceof Item) {
                                if ($player->getInventory()->canAddItem($chosenItem)) {
                                    $player->getInventory()->addItem($chosenItem);
                                    $player->sendToastNotification(C::colorize("&r&l&d(!) &r&7You won " . $chosenItem->getCount() . "x &l" . $chosenItem->getName() . "&r&7 from the &r&d&lVote Crate"), C::colorize("&r&7(/vote)!"));
                                } else {
                                    $chosenItem->setCount(1);
                                    $player->getWorld()->dropItem($player->getPosition(), $chosenItem);
                                    $keysToOpen--;
                                    break;
                                }
                            } else {
                                $player->sendMessage("Error: No item chosen");
                            }
                        }
        
                        $item->setCount($keysInHand - $keysToOpen);
                        $player->getInventory()->setItemInHand($item);
                        return;
                    }

                    foreach ($voteItems as $itemData) {
                        $randomNumber -= $itemData["chance"];
                        if ($randomNumber <= 0) {
                            $chosenItem = $itemData["item"];
                            break;
                        }
                    }

                    if ($chosenItem instanceof Item) {
                        $player->getInventory()->addItem($chosenItem);
                        $player->sendToastNotification(C::colorize("&r&l&d(!) &r&7You won " . $chosenItem->getCount(). "x &l" . $chosenItem->getName() . "&r&7 from the &r&d&lVote Crate"), C::colorize("&r&7(/vote)!"));
                    } else {
                        $player->sendMessage("Error: No item chosen");
                    }

                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    return;
                }
            } elseif ($block->getPosition()->floor()->equals($voteCrate) && $block instanceof EnderChest && $event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                CratePreviews::getVoteCratePreview()->send($player);
            } else {
                $player->sendToastNotification(C::colorize("&r&c&l(!) &r&cYou must have a &l&dVote Crate Key &r&cto open"), C::colorize("&r&c this crate! You can obtain vote keys by voting with &d/vote&c!"));
                return;
            }
        }

        if ($block->getPosition()->floor()->equals($cipherCrate)) {
            $event->cancel();
            if (($tag = $item->getNamedTag()->getTag("crate_key")) !== null && $block instanceof EnderChest && $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                $keyValue = $tag->getValue();
                if ($keyValue === "cipher") {
                    $cipherItems = [
                        ["item" => PouchItem::getPouchType("unique"), "chance" => 15],
                        ["item" => PouchItem::getPouchType("unique_gem"), "chance" => 10],
                        ["item" => PouchItem::getPouchType("unique_xp"), "chance" => 15],
                        ["item" => Items::getCrateKey("zenith"), "chance" => 15],
                        ["item" => Items::getCrateKey("cipher", 2), "chance" => 10],
                        ["item" => Items::createRandomCEBook("unique", 1), "chance" => 15],
                        ["item" => Items::createRandomCEBook("elite", 1), "chance" => 10],
                        ["item" => Items::getEnchantScrolls("whitescroll", 1), "chance" => 15],
                        ["item" => Items::getEnchantScrolls("blackscroll", 1), "chance" => 15],
                        ["item" => VanillaBlocks::IRON()->asItem(), "chance" => 15],
                        ["item" => StringToItemParser::getInstance()->parse("pig_spawner"), "chance" => 15],
                        ["item" => Items::getEnchantScrolls("itemrename", 1), "chance" => 15],
                        ["item" => Items::getEnchantScrolls("lorecrystal", 1), "chance" => 15],
                    ];

                    $totalChance = array_reduce($cipherItems, function ($carry, $item) {
                        return $carry + $item["chance"];
                    }, 0);

                    $randomNumber = mt_rand(0, $totalChance);

                    $chosenItem = null;

                    if ($player->isSneaking()) {
                        $keysInHand = $item->getCount();
                        $keysToOpen = $keysInHand;
        
                        for ($i = 0; $i < $keysInHand; $i++) {
                            $totalChance = array_reduce($cipherItems, function ($carry, $item) {
                                return $carry + $item["chance"];
                            }, 0);
        
                            $randomNumber = mt_rand(0, $totalChance);
        
                            $chosenItem = null;
                            foreach ($cipherItems as $itemData) {
                                $randomNumber -= $itemData["chance"];
                                if ($randomNumber <= 0) {
                                    $chosenItem = $itemData["item"];
                                    break;
                                }
                            }
        
                            if ($chosenItem instanceof Item) {
                                if ($player->getInventory()->canAddItem($chosenItem)) {
                                    $player->getInventory()->addItem($chosenItem);
                                    $player->sendToastNotification(C::colorize("&r&l&d(!) &r&7You won " . $chosenItem->getCount() . "x &l" . $chosenItem->getName() . "&r&7 from the &r&f&lCipher Crate"), C::colorize("&r&7(Tier 1)!"));
                                } else {
                                    $chosenItem->setCount(1);
                                    $player->getWorld()->dropItem($player->getPosition(), $chosenItem);
                                    $keysToOpen--;
                                    break;
                                }
                            } else {
                                $player->sendMessage("Error: No item chosen");
                            }
                        }
        
                        $item->setCount($keysInHand - $keysToOpen);
                        $player->getInventory()->setItemInHand($item);
                        return;
                    }

                    foreach ($cipherItems as $itemData) {
                        $randomNumber -= $itemData["chance"];
                        if ($randomNumber <= 0) {
                            $chosenItem = $itemData["item"];
                            break;
                        }
                    }

                    if ($chosenItem instanceof Item) {
                        $player->getInventory()->addItem($chosenItem);
                        $player->sendToastNotification(C::colorize("&r&l&f(!) &r&7You won " . $chosenItem->getCount(). "x &l" . $chosenItem->getName() . "&r&7 from the &r&&lCipher Crate"), C::colorize("&r&7(Tier 1)!"));
                    } else {
                        $player->sendMessage("Error: No item chosen");
                    }

                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    return;
                }
            } elseif ($block->getPosition()->floor()->equals($cipherCrate) && $block instanceof EnderChest && $event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                CratePreviews::getCipherCratePreview()->send($player);
            } else {
                $player->sendToastNotification(C::colorize("&r&c&l(!) &r&cYou must have a &l&fCipher Crate Key &r&cin your hand to open"), C::colorize("&r&cthis crate! You can purchase crate keys at &fbuy.etherealhub.net&c!"));
            }
        }

        if ($block->getPosition()->floor()->equals($zenithCrate) && $block instanceof EnderChest) {
            $event->cancel();
            if (($tag = $item->getNamedTag()->getTag("crate_key")) !== null && $block instanceof EnderChest && $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                $keyValue = $tag->getValue();
                if ($keyValue === "zenith") {
                    $zenithItems = [
                        ["item" => PouchItem::getPouchType("elite"), "chance" => 20],
                        ["item" => PouchItem::getPouchType("elite_gem"), "chance" => 10],
                        ["item" => PouchItem::getPouchType("elite_xp"), "chance" => 15],
                        ["item" => Items::getCrateKey("empyrean"), "chance" => 15],
                        ["item" =>  Items::createRandomCEBook("elite", 1), "chance" => 20],
                        ["item" =>  Items::createRandomCEBook("ultimate", 1), "chance" => 20],
                        ["item" =>  StringToItemParser::getInstance()->parse("blaze_spawner")->setCount(1), "chance" => 20],
                        ["item" =>  Items::createPerkVoucher("randomizer"), "chance" => 20],
                        ["item" =>  Items::createEnchantFragment("thorns", 1), "chance" => 15],
                        ["item" =>  Items::createEnchantFragment("fire_aspect", 1), "chance" => 15],
                        ["item" =>  Items::getEnchantScrolls("playerkillcounter", 1), "chance" => 15],
                        ["item" =>  VanillaBlocks::DIAMOND()->asItem()->setCount(4), "chance" => 15],
                    ];

                    $totalChance = array_reduce($zenithItems, function ($carry, $item) {
                        return $carry + $item["chance"];
                    }, 0);

                    $randomNumber = mt_rand(0, $totalChance);

                    $chosenItem = null;

                    if ($player->isSneaking()) {
                        $keysInHand = $item->getCount();
                        $keysToOpen = $keysInHand;
        
                        for ($i = 0; $i < $keysInHand; $i++) {
                            $totalChance = array_reduce($zenithItems, function ($carry, $item) {
                                return $carry + $item["chance"];
                            }, 0);
        
                            $randomNumber = mt_rand(0, $totalChance);
        
                            $chosenItem = null;
                            foreach ($zenithItems as $itemData) {
                                $randomNumber -= $itemData["chance"];
                                if ($randomNumber <= 0) {
                                    $chosenItem = $itemData["item"];
                                    break;
                                }
                            }
        
                            if ($chosenItem instanceof Item) {
                                if ($player->getInventory()->canAddItem($chosenItem)) {
                                    $player->getInventory()->addItem($chosenItem);
                                    $player->sendToastNotification(C::colorize("&r&l&d(!) &r&7You won " . $chosenItem->getCount() . "x &l" . $chosenItem->getName() . "&r&7 from the &r&9&lZenith Crate"), C::colorize("&r&7(/Tier 2)!"));
                                } else {
                                    $chosenItem->setCount(1);
                                    $player->getWorld()->dropItem($player->getPosition(), $chosenItem);
                                    $keysToOpen--;
                                    break;
                                }
                            } else {
                                $player->sendMessage("Error: No item chosen");
                            }
                        }
        
                        $item->setCount($keysInHand - $keysToOpen);
                        $player->getInventory()->setItemInHand($item);
                        return;
                    }

                    foreach ($zenithItems as $itemData) {
                        $randomNumber -= $itemData["chance"];
                        if ($randomNumber <= 0) {
                            $chosenItem = $itemData["item"];
                            break;
                        }
                    }

                    if ($chosenItem instanceof Item) {
                        $player->getInventory()->addItem($chosenItem);
                        $player->sendToastNotification(C::colorize("&r&l&9(!) &r&7You won " . $chosenItem->getCount(). "x &l" . $chosenItem->getName() . "&r&7 from the &r&9&lZenith Crate"), C::colorize("&r&7(Tier 2)!"));
                    } else {
                        $player->sendMessage("Error: No item chosen");
                    }

                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    return;
                }
            } elseif ($block->getPosition()->floor()->equals($zenithCrate) && $block instanceof EnderChest && $event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                CratePreviews::getZenithCratePreview()->send($player);
            } else {
                $player->sendToastNotification(C::colorize("&r&c&l(!) &r&cYou must have a &l&9Zenith Crate Key &r&cin your hand to open"), C::colorize("&r&cthis crate! You can purchase zenith keys at &fbuy.etherealhub.net&c!"));
            }
        }

        if ($block->getPosition()->floor()->equals($empyreanCrate) && $block instanceof EnderChest) {
            $event->cancel();
            if (($tag = $item->getNamedTag()->getTag("crate_key")) !== null && $block instanceof EnderChest && $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                $keyValue = $tag->getValue();
                if ($keyValue === "empyrean") {
                    $empyreanItems = [
                        ["item" => PouchItem::getPouchType("ultimate"), "chance" => 20],
                        ["item" => PouchItem::getPouchType("ultimate_gem"), "chance" => 10],
                        ["item" => PouchItem::getPouchType("ultimate_xp"), "chance" => 15],
                        ["item" => Items::getCrateKey("empyrean")->setCount(2), "chance" => 15],
                        ["item" => Items::createPerkVoucher("randomizer", 2), "chance" => 20],
                        ["item" => Items::createBossEgg("broodmother"), "chance" => 15],
                        ["item" => Items::createRandomCEBook("ultimate", 3), "chance" => 15],
                        ["item" => Items::createRandomCEBook("legendary", 3), "chance" => 15],
                        ["item" => Items::getEnchantScrolls("whitescroll", 2), "chance" => 15],
                        ["item" => Items::getEnchantScrolls("blackscroll", 2, 50), "chance" => 15],
                        ["item" => Items::createEnchantFragment("fortune"), "chance" => 15],
                        ["item" => Items::createEnchantFragment("depth_strider"), "chance" => 15],
                        ["item" => Items::createEnchantFragment("looting"), "chance" => 15],    
                        ["item" => Items::createRankVoucher("ascendant"), "chance" => 15],
                    ];

                    $totalChance = array_reduce($empyreanItems, function ($carry, $item) {
                        return $carry + $item["chance"];
                    }, 0);

                    $randomNumber = mt_rand(0, $totalChance);

                    $chosenItem = null;
                    
                    if ($player->isSneaking()) {
                        $keysInHand = $item->getCount();
                        $keysToOpen = $keysInHand;
        
                        for ($i = 0; $i < $keysInHand; $i++) {
                            $totalChance = array_reduce($empyreanItems, function ($carry, $item) {
                                return $carry + $item["chance"];
                            }, 0);
        
                            $randomNumber = mt_rand(0, $totalChance);
        
                            $chosenItem = null;
                            foreach ($empyreanItems as $itemData) {
                                $randomNumber -= $itemData["chance"];
                                if ($randomNumber <= 0) {
                                    $chosenItem = $itemData["item"];
                                    break;
                                }
                            }
        
                            if ($chosenItem instanceof Item) {
                                if ($player->getInventory()->canAddItem($chosenItem)) {
                                    $player->getInventory()->addItem($chosenItem);
                                    $player->sendToastNotification(C::colorize("&r&l&d(!) &r&7You won " . $chosenItem->getCount() . "x &l" . $chosenItem->getName() . "&r&7 from the &r&c&lEmpyrean Crate"), C::colorize("&r&7(/Tier 3)!"));
                                } else {
                                    $chosenItem->setCount(1);
                                    $player->getWorld()->dropItem($player->getPosition(), $chosenItem);
                                    $keysToOpen--;
                                    break;
                                }
                            } else {
                                $player->sendMessage("Error: No item chosen");
                            }
                        }
        
                        $item->setCount($keysInHand - $keysToOpen);
                        $player->getInventory()->setItemInHand($item);
                        return;
                    }

                    foreach ($empyreanItems as $itemData) {
                        $randomNumber -= $itemData["chance"];
                        if ($randomNumber <= 0) {
                            $chosenItem = $itemData["item"];
                            break;
                        }
                    }

                    if ($chosenItem instanceof Item) {
                        $player->getInventory()->addItem($chosenItem);
                        $player->sendToastNotification(C::colorize("&r&l&c(!) &r&7You won " . $chosenItem->getCount(). "x &l" . $chosenItem->getName() . "&r&7 from the &r&c&lEmpyrean Crate"), C::colorize("&r&7(Tier 3)!"));
                    } else {
                        $player->sendMessage("Error: No item chosen");
                    }

                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    return;
                }
            } elseif ($block->getPosition()->floor()->equals($empyreanCrate) && $block instanceof EnderChest && $event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                CratePreviews::getEmpyreanCratePreview()->send($player);
            } else {
                $player->sendToastNotification(C::colorize("&r&c&l(!) &r&cYou must have a &l&cEmpyrean Crate Key &r&cin your hand to open"), C::colorize("&r&cthis crate! You can purchase empyrean keys at &fbuy.etherealhub.net&c!"));
            }
        }
    }
}