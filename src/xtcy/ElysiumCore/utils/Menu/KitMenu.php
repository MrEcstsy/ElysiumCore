<?php

namespace xtcy\ElysiumCore\utils\Menu;

use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Armor;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;

class KitMenu
{

    public static function getKitCategoryMenu(Player $player): InvMenu {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $inventory = $menu->getInventory();

        $menu->setName(C::colorize("&r&8Kits"));
        Utils::fillInventory($inventory, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK)->asItem(), [12, 14]);

        $inventory->setItem(12, VanillaItems::CLOCK()->setCustomName(C::colorize("&r&l&eRank Kits"))->setLore([
            C::colorize("&r&7These kits are unlocked with ranks."),
            "",
            C::colorize("&r&estore.etherealhub.tk")
        ]));
        $inventory->setItem(14, VanillaItems::BOOK()->setCustomName(C::colorize("&r&l&aGlobal Kits"))->setLore([
            C::colorize("&r&7These kits are unlocked globally."),
            "",
            C::colorize("&r&astore.etherealhub.tk")
        ]));


        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction): void {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();

            switch ($slot) {
                case 12:
                    self::getRankKitsMenu($player)->send($player);
                    break;
                case 14:

                    break;
            }

        }));


        return $menu;
    }

    public static function getRankKitsMenu(Player $player): InvMenu
    {
        $menu = CustomSizedInvMenu::create(9);
        $inventory = $menu->getInventory();
        $session = Loader::getPlayerManager()->getSession($player);
        $menu->setName(C::colorize("&r&8Rank Kits"));


        if ($session->getCooldown("kit.starter") !== null && $session->getCooldown("kit.starter") !== 0) {
            $inventory->setItem(0, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE)->asItem()->setCustomName(C::colorize("&r&l&7Starter"))->setLore([
                "",
                C::colorize("&r&l&6ON COOLDOWN"),
                C::colorize("&r&7This kit is currently on &6cooldown&7."),
                C::colorize(" &r&6➥ " . Utils::translateTime($session->getCooldown("kit.starter"))),
                "",
                C::colorize("&r&7Use /kit preview starter to &6preview &7kit."),
            ]));
        } elseif ($player->hasPermission("kit.starter")) {
            $inventory->setItem(0, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN)->asItem()->setCustomName(C::colorize("&r&l&7Starter"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.starter"),
                "",
                C::colorize("&r&7Click to &areceive &7kit."),
                "",
                C::colorize("&r&7Use /kit preview starter to &apreview &7kit."),
            ]));
        } elseif (!$player->hasPermission("kit.starter")) {
            $inventory->setItem(0, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED)->asItem()->setCustomName(C::colorize("&r&l&7Starter"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.starter"),
                "",
                C::colorize("&r&7You &cdo not have access&7 to this kit."),
                "",
                C::colorize("&r&7Use /kit preview starter to &cpreview &7kit."),
            ]));
        }

        //////// seeker kit ////////

        if (!$player->hasPermission("kit.seeker") && ($session->getCooldown("kit.seeker") === null || $session->getCooldown("kit.seeker") === 0)) {
            $inventory->setItem(1, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED)->asItem()->setCustomName(C::colorize("&r&l&bSeeker"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.seeker"),
                "",
                C::colorize("&r&7You &cdo not have access&7 to this kit."),
                "",
                C::colorize("&r&7Use /kit preview seeker to &cpreview &7kit."),
            ]));
        } elseif ($session->getCooldown("kit.seeker") !== null && $session->getCooldown("kit.seeker") !== 0) {
            $inventory->setItem(1, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE)->asItem()->setCustomName(C::colorize("&r&l&bSeeker"))->setLore([
                "",
                C::colorize("&r&l&6ON COOLDOWN"),
                C::colorize("&r&7This kit is currently on &6cooldown&7."),
                C::colorize(" &r&6➥ " . Utils::translateTime($session->getCooldown("kit.seeker"))),
                "",
                C::colorize("&r&7Use /kit preview seeker to &6preview &7kit."),
            ]));
        } elseif ($player->hasPermission("kit.seeker")) {
            $inventory->setItem(1, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN)->asItem()->setCustomName(C::colorize("&r&l&bSeeker"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.seeker"),
                "",
                C::colorize("&r&7Click to &breceive &7kit."),
                "",
                C::colorize("&r&7Use /kit preview seeker to &bpreview &7kit."),
            ]));
        }

        //////// luminary kit ////////

        if (!$player->hasPermission("kit.luminary") && ($session->getCooldown("kit.luminary") === null || $session->getCooldown("kit.luminary") === 0)) {
            $inventory->setItem(2, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED)->asItem()->setCustomName(C::colorize("&r&l&gLuminary"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.luminary"),
                "",
                C::colorize("&r&7You &cdo not have access&7 to this kit."),
                "",
                C::colorize("&r&7Use /kit preview luminary to &cpreview &7kit."),
            ]));
        } elseif ($session->getCooldown("kit.luminary") !== null && $session->getCooldown("kit.luminary") !== 0) {
            $inventory->setItem(2, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE)->asItem()->setCustomName(C::colorize("&r&l&gLuminary"))->setLore([
                "",
                C::colorize("&r&l&6ON COOLDOWN"),
                C::colorize("&r&7This kit is currently on &6cooldown&7."),
                C::colorize(" &r&6➥ " . Utils::translateTime($session->getCooldown("kit.luminary"))),
                "",
                C::colorize("&r&7Use /kit preview luminary to &6preview &7kit."),
            ]));
        } elseif ($player->hasPermission("kit.luminary")) {
            $inventory->setItem(2, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN)->asItem()->setCustomName(C::colorize("&r&l&gLuminary"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.luminary"),
                "",
                C::colorize("&r&7Click to &greceive &7kit."),
                "",
                C::colorize("&r&7Use /kit preview luminary to &gluminary &7kit."),
            ]));
        }

        /////// celestial kit ////////

        if (!$player->hasPermission("kit.celestial") && ($session->getCooldown("kit.celestial") === null || $session->getCooldown("kit.celestial") === 0)) {
            $inventory->setItem(3, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED)->asItem()->setCustomName(C::colorize("&r&l&dCelestial"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.celestial"),
                "",
                C::colorize("&r&7You &cdo not have access&7 to this kit."),
                "",
                C::colorize("&r&7Use /kit preview celestial to &cpreview &7kit."),
            ]));
        } elseif ($session->getCooldown("kit.celestial") !== null && $session->getCooldown("kit.celestial") !== 0) {
            $inventory->setItem(3, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE)->asItem()->setCustomName(C::colorize("&r&l&dCelestial"))->setLore([
                "",
                C::colorize("&r&l&6ON COOLDOWN"),
                C::colorize("&r&7This kit is currently on &6cooldown&7."),
                C::colorize(" &r&6➥ " . Utils::translateTime($session->getCooldown("kit.celestial"))),
                "",
                C::colorize("&r&7Use /kit preview celestial to &6preview &7kit."),
            ]));
        } elseif ($player->hasPermission("kit.celestial")) {
            $inventory->setItem(3, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN)->asItem()->setCustomName(C::colorize("&r&l&dCelestial"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.celestial"),
                "",
                C::colorize("&r&7Click to &dreceive &7kit."),
                "",
                C::colorize("&r&7Use /kit preview celestial to &dcelestial &7kit."),
            ]));
        }

        /////// elysian kit ////////

        if (!$player->hasPermission("kit.elysian") && ($session->getCooldown("kit.elysian") === null || $session->getCooldown("kit.elysian") === 0)) {
            $inventory->setItem(4, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED)->asItem()->setCustomName(C::colorize("&r&l&3Elysian"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.elysian"),
                "",
                C::colorize("&r&7You &cdo not have access&7 to this kit."),
                "",
                C::colorize("&r&7Use /kit preview elysian to &cpreview &7kit."),
            ]));
        } elseif ($session->getCooldown("kit.celestial") !== null && $session->getCooldown("kit.celestial") !== 0) {
            $inventory->setItem(4, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE)->asItem()->setCustomName(C::colorize("&r&l&3Elysian"))->setLore([
                "",
                C::colorize("&r&l&6ON COOLDOWN"),
                C::colorize("&r&7This kit is currently on &6cooldown&7."),
                C::colorize(" &r&6➥ " . Utils::translateTime($session->getCooldown("kit.elysian"))),
                "",
                C::colorize("&r&7Use /kit preview elysian to &6preview &7kit."),
            ]));
        } elseif ($player->hasPermission("kit.elysian")) {
            $inventory->setItem(4, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN)->asItem()->setCustomName(C::colorize("&r&l&3Elysian"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.elysian"),
                "",
                C::colorize("&r&7Click to &3receive &7kit."),
                "",
                C::colorize("&r&7Use /kit preview elysian to &3elysian &7kit."),
            ]));
        }

        /////// Ascendant kit ////////

        if (!$player->hasPermission("kit.ascendant") && ($session->getCooldown("kit.ascendant") === null || $session->getCooldown("kit.ascendant") === 0)) {
            $inventory->setItem(5, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED)->asItem()->setCustomName(C::colorize("&r&l&6Ascendant"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.ascendant"),
                "",
                C::colorize("&r&7You &cdo not have access&7 to this kit."),
                "",
                C::colorize("&r&7Use /kit preview ascendant to &cpreview &7kit."),
            ]));
        } elseif ($session->getCooldown("kit.ascendant") !== null && $session->getCooldown("kit.ascendant") !== 0) {
            $inventory->setItem(5, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE)->asItem()->setCustomName(C::colorize("&r&l&6Ascendant"))->setLore([
                "",
                C::colorize("&r&l&6ON COOLDOWN"),
                C::colorize("&r&7This kit is currently on &6cooldown&7."),
                C::colorize(" &r&6➥ " . Utils::translateTime($session->getCooldown("kit.ascendant"))),
                "",
                C::colorize("&r&7Use /kit preview ascendant to &6preview &7kit."),
            ]));
        } elseif ($player->hasPermission("kit.ascendant")) {
            $inventory->setItem(5, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN)->asItem()->setCustomName(C::colorize("&r&l&6Ascendant"))->setLore([
                "",
                Utils::getPermissionLockedStatus($player, "kit.ascendant"),
                "",
                C::colorize("&r&7Click to &6receive &7kit."),
                "",
                C::colorize("&r&7Use /kit preview ascendant to &6ascendant &7kit."),
            ]));
        }

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use($session): void {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();

            if ($slot === 0 && ($session->getCooldown("kit.starter") === null || $session->getCooldown("kit.starter") === 0)) {
                $session->addCooldown("kit.starter", 86400);
                $starterKit = [
                    VanillaItems::IRON_HELMET(),
                    VanillaItems::IRON_CHESTPLATE(),
                    VanillaItems::IRON_LEGGINGS(),
                    VanillaItems::IRON_BOOTS(),
                    VanillaItems::IRON_SWORD(),
                    VanillaItems::DIAMOND_PICKAXE(),
                    VanillaItems::DIAMOND_SHOVEL(),
                    VanillaItems::DIAMOND_AXE(),
                    VanillaItems::STEAK()->setCount(64),
                ];

                foreach ($starterKit as $item) {
                    if ($item instanceof Armor) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                    } elseif ($item instanceof Sword) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3));
                    } elseif ($item instanceof Tool) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 3));
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                    }


                    $player->getInventory()->addItem($item);
                    $player->removeCurrentWindow();
                }
            }

            if ($slot === 1 && ($session->getCooldown("kit.seeker") === null || $session->getCooldown("kit.seeker") === 0)) {
                if ($player->hasPermission("kit.seeker")) {
                    $session->addCooldown("kit.seeker", 86400);
                    $seekerKit = [
                        VanillaItems::DIAMOND_HELMET(),
                        VanillaItems::DIAMOND_CHESTPLATE(),
                        VanillaItems::DIAMOND_LEGGINGS(),
                        VanillaItems::DIAMOND_BOOTS(),
                        VanillaItems::DIAMOND_SWORD(),
                        VanillaItems::DIAMOND_PICKAXE(),
                        VanillaItems::DIAMOND_SHOVEL(),
                        VanillaItems::DIAMOND_AXE(),
                        VanillaItems::GOLDEN_APPLE()->setCount(2),
                        VanillaItems::BOW(),
                        VanillaItems::ARROW()->setCount(64),
                    ];

                    foreach ($seekerKit as $item) {

                        if ($item instanceof Armor) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                        } elseif ($item instanceof Sword) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2));
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("looting"), 2));
                        } elseif ($item instanceof Tool && $item->getTypeId() !== ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 2));
                        }
                        if ($item->getTypeId() === ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FLAME(), 1));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                        }

                        if ($item->getTypeId() === ItemTypeIds::DIAMOND_BOOTS) {
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("depth_strider"), 3));
                        }

                        $player->getInventory()->addItem($item);
                        $player->removeCurrentWindow();
                    }
                }
            }

            if ($slot === 2 && ($session->getCooldown("kit.luminary") === null || $session->getCooldown("kit.luminary") === 0)) {
                if ($player->hasPermission("kit.luminary")) {

                    $session->addCooldown("kit.luminary", 86400);
                    $luminaryKit = [
                        VanillaItems::DIAMOND_HELMET(),
                        VanillaItems::DIAMOND_CHESTPLATE(),
                        VanillaItems::DIAMOND_LEGGINGS(),
                        VanillaItems::DIAMOND_BOOTS(),
                        VanillaItems::DIAMOND_SWORD(),
                        VanillaItems::DIAMOND_PICKAXE(),
                        VanillaItems::DIAMOND_SHOVEL(),
                        VanillaItems::DIAMOND_AXE(),
                        VanillaItems::GOLDEN_APPLE()->setCount(2),
                        VanillaItems::BOW(),
                        VanillaItems::ARROW()->setCount(64),
                    ];

                    foreach ($luminaryKit as $item) {

                        if ($item instanceof Armor) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                        } elseif ($item instanceof Sword) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3));
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("looting"), 2));
                        } elseif ($item instanceof Tool && $item->getTypeId() !== ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 2));
                        }

                        if ($item->getTypeId() === ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FLAME(), 1));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                        }

                        if ($item->getTypeId() === ItemTypeIds::DIAMOND_BOOTS) {
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("depth_strider"), 3));
                        }

                        $player->getInventory()->addItem($item);
                        $player->removeCurrentWindow();
                    }
                }
            }

            if ($slot === 3 && ($session->getCooldown("kit.celestial") === null || $session->getCooldown("kit.celestial") === 0)) {
                if ($player->hasPermission("kit.celestial")) {
                    $session->addCooldown("kit.celestial", 86400);
                    $celestialKit = [
                        VanillaItems::DIAMOND_HELMET(),
                        VanillaItems::DIAMOND_CHESTPLATE(),
                        VanillaItems::DIAMOND_LEGGINGS(),
                        VanillaItems::DIAMOND_BOOTS(),
                        VanillaItems::DIAMOND_SWORD(),
                        VanillaItems::DIAMOND_PICKAXE(),
                        VanillaItems::DIAMOND_SHOVEL(),
                        VanillaItems::DIAMOND_AXE(),
                        VanillaItems::GOLDEN_APPLE()->setCount(4),
                        VanillaItems::BOW(),
                        VanillaItems::ARROW()->setCount(1),
                    ];

                    foreach ($celestialKit as $item) {

                        if ($item instanceof Armor) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                        } elseif ($item instanceof Sword) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3));
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("looting"), 2));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT(), 2));
                        } elseif ($item instanceof Tool && $item->getTypeId() !== ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 4));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 3));
                        }
                        if ($item->getTypeId() === ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FLAME(), 1));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                        }

                        if ($item->getTypeId() === ItemTypeIds::DIAMOND_BOOTS) {
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("depth_strider"), 3));
                        }

                        $player->getInventory()->addItem($item);
                        $player->removeCurrentWindow();
                    }
                }
            }

            if ($slot === 3 && ($session->getCooldown("kit.elysian") === null || $session->getCooldown("kit.elysian") === 0)) {
                if ($player->hasPermission("kit.elysian")) {
                    $session->addCooldown("kit.elysian", 86400);
                    $ascendantKit = [
                        VanillaItems::DIAMOND_HELMET(),
                        VanillaItems::DIAMOND_CHESTPLATE(),
                        VanillaItems::DIAMOND_LEGGINGS(),
                        VanillaItems::DIAMOND_BOOTS(),
                        VanillaItems::DIAMOND_SWORD(),
                        VanillaItems::DIAMOND_PICKAXE(),
                        VanillaItems::DIAMOND_SHOVEL(),
                        VanillaItems::DIAMOND_AXE(),
                        VanillaItems::GOLDEN_APPLE()->setCount(6),
                        VanillaItems::BOW(),
                        VanillaItems::ARROW()->setCount(1),
                    ];

                    foreach ($ascendantKit as $item) {
                        if ($item instanceof Armor) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                        } elseif ($item instanceof Sword) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 4));
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("looting"), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT(), 2));
                        } elseif ($item instanceof Tool && $item->getTypeId() !== ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 4));
                        }

                        if ($item->getTypeId() === ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FLAME(), 1));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                        }

                        if ($item->getTypeId() === ItemTypeIds::DIAMOND_BOOTS) {
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("depth_strider"), 3));
                        }

                        $player->getInventory()->addItem($item);
                        $player->removeCurrentWindow();
                    }
                }
            }

            if ($slot === 5 && ($session->getCooldown("kit.ascendant") === null || $session->getCooldown("kit.ascendant") === 0)) {
                if ($player->hasPermission("kit.ascendant")) {
                    $session->addCooldown("kit.ascendant", 86400);
                    $ascendantKit = [
                        VanillaItems::DIAMOND_HELMET(),
                        VanillaItems::DIAMOND_CHESTPLATE(),
                        VanillaItems::DIAMOND_LEGGINGS(),
                        VanillaItems::DIAMOND_BOOTS(),
                        VanillaItems::DIAMOND_SWORD(),
                        VanillaItems::DIAMOND_PICKAXE(),
                        VanillaItems::DIAMOND_SHOVEL(),
                        VanillaItems::DIAMOND_AXE(),
                        VanillaItems::GOLDEN_APPLE()->setCount(6),
                        VanillaItems::BOW(),
                        VanillaItems::ARROW()->setCount(1),
                    ];

                    foreach ($ascendantKit as $item) {
                        if ($item instanceof Armor) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 4));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                        } elseif ($item instanceof Sword) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 4));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5));
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("looting"), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT(), 2));
                        } elseif ($item instanceof Tool) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 5));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 4));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FORTUNE(), 4));
                        }

                        if ($item->getTypeId() === ItemTypeIds::BOW) {
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FLAME(), 1));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 3));
                            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                        }

                        if ($item->getTypeId() === ItemTypeIds::DIAMOND_BOOTS) {
                            $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse("depth_strider"), 3));
                        }

                        $player->getInventory()->addItem($item);
                        $player->removeCurrentWindow();
                    }
                }
            }

        }));
        return $menu;
    }
}