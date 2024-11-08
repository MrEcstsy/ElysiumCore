<?php

namespace xtcy\ElysiumCore\utils\Menu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\LuckyPouches\utils\PouchItem;
use xtcy\ElysiumCore\items\Items;

class CratePreviews
{

    public static function getVoteCratePreview(): InvMenu {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $inventory = $menu->getInventory();
        $lootTable = [
            PouchItem::getPouchType("simple"),
            PouchItem::getPouchType("simple_gem"),
            PouchItem::getPouchType("simple_xp"),
            Items::getCrateKey("vote", 2),
            Items::getCrateKey("cipher"),
            Items::getCrateKey("zenith"), 
            Items::getCrateKey("empyrean"),
            Items::createBankNote(null, 10000), 
            Items::createBankNote(null, 25000),
            Items::createBankNote(null, 50000),
            Items::createExperienceBottle(null, 1000),
            Items::createExperienceBottle(null, 2500), 
            Items::createExperienceBottle(null, 5000),
            Items::createEnchantFragment("unbreaking", 1),
            Items::createRandomCEBook("simple", 1),
            Items::createRandomCEBook("unique", 1),
            Items::createRankVoucher("seeker", 1),
            Items::createRankVoucher("luminary", 1),
            Items::createRankVoucher("celestial", 1),
            Items::createRankVoucher("elysian", 1),
            Items::createRankVoucher("ascendant", 1),
        ];

        foreach ($lootTable as $item) {
            $inventory->addItem($item);
        }

        $menu->setName(C::colorize("&r&l&dVote Crate &r&7(/vote)"));
        $menu->setListener(InvMenu::readonly());

        return $menu;
    }

    public static function getCipherCratePreview(): InvMenu {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $inventory = $menu->getInventory();

        $items = [
            PouchItem::getPouchType("unique"),
            PouchItem::getPouchType("unique_gem"),
            PouchItem::getPouchType("unique_xp"),
            Items::getCrateKey("zenith"),
            Items::getCrateKey("cypher", 2),
            Items::createRandomCEBook("unique", 1),
            Items::createRandomCEBook("elite", 1),
            Items::getEnchantScrolls("whitescroll"),
            Items::getEnchantScrolls("blackscroll"),
            VanillaBlocks::IRON()->asItem()->setCount(8),
            StringToItemParser::getInstance()->parse("pig_spawner")->setCount(1),
            Items::getEnchantScrolls("itemrename", 1),
            Items::getEnchantScrolls("lorecrystal", 1),
        ];

        foreach ($items as $item) {
            $inventory->addItem($item);
        }

        $menu->setName(C::colorize("&r&l&fCipher Crate &r&7(Tier 1)"));
        $menu->setListener(InvMenu::readonly());

        return $menu;
    }

    public static function getZenithCratePreview(): InvMenu
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $inventory = $menu->getInventory();

        $items = [
            PouchItem::getPouchType("elite"),
            PouchItem::getPouchType("elite_gem"),
            PouchItem::getPouchType("elite_xp"),
            Items::getCrateKey("zenith"),
            Items::createRandomCEBook("elite", 1),
            Items::createRandomCEBook("ultimate", 1),
            StringToItemParser::getInstance()->parse("blaze_spawner")->setCount(1),
            Items::createPerkVoucher("randomizer"),
            Items::createEnchantFragment("thorns", 1),
            Items::createEnchantFragment("fire_aspect", 1),
            Items::getEnchantScrolls("playerkillcounter", 1),
            VanillaBlocks::DIAMOND()->asItem()->setCount(4),
        ];

        foreach ($items as $item) {
            $inventory->addItem($item);
        }

        $menu->setName(C::colorize("&r&l&9Zenith Crate &r&7(Tier 2)"));
        $menu->setListener(InvMenu::readonly());
        return $menu;
    }

    public static function getEmpyreanCratePreview(): InvMenu
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $inventory = $menu->getInventory();

        $items = [
            PouchItem::getPouchType("ultimate"),
            PouchItem::getPouchType("ultimate_gem"),
            PouchItem::getPouchType("ultimate_xp"),
            Items::getCrateKey("empyrean"),
            Items::createPerkVoucher("randomizer", 2),
            Items::createBossEgg("broodmother"),
            Items::createRandomCEBook("ultimate", 3),
            Items::createRandomCEBook("legendary", 3),
            Items::getEnchantScrolls("whitescroll", 2),
            Items::getEnchantScrolls("blackscroll", 2, 50),
            Items::createEnchantFragment("fortune"),
            Items::createEnchantFragment("depth_strider"),
            Items::createEnchantFragment("looting"),
            Items::createRankVoucher("ascendant", 1),
        ];

        foreach ($items as $item) {
            $inventory->addItem($item);
        }

        $menu->setName(C::colorize("&r&l&cEmpyrean Crate &r&7(Tier 3)"));
        $menu->setListener(InvMenu::readonly());
        return $menu;
    }
}