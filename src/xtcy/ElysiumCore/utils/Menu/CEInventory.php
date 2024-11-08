<?php

namespace xtcy\ElysiumCore\utils\Menu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\ActorFixedInvMenuType;
use muqsit\invmenu\type\FixedInvMenuType;
use muqsit\invmenu\type\InvMenuType;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\inventory\ChestInventory;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use xtcy\ElysiumCore\utils\RarityType;

class CEInventory
{
    public Player $player;

    public readonly InvMenuType $type;

    public static function createInventory(): InvMenu
    {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setName("Custom Enchants");

        $book = VanillaItems::BOOK()->setLore(["", "§7Click to view Custom Enchants", "§7of this rarity"]);

        $menu->getInventory()->setItem(10, $book->setCustomName(RarityType::SIMPLE()->getCustomName() . " Enchants"));
        $menu->getInventory()->setItem(11, $book->setCustomName(RarityType::UNIQUE()->getCustomName() . " Enchants"));
        $menu->getInventory()->setItem(12, $book->setCustomName(RarityType::ELITE()->getCustomName() . " Enchants"));
        $menu->getInventory()->setItem(13, $book->setCustomName(RarityType::ULTIMATE()->getCustomName() . " Enchants"));
        $menu->getInventory()->setItem(14, $book->setCustomName(RarityType::LEGENDARY()->getCustomName() . " Enchants"));
        //$menu->getInventory()->setItem(15, $book->setCustomName(RarityType::HEROIC()->getCustomName() . " Enchants"));

        foreach ($menu->getInventory()->getContents(true) as $k => $v) {
            if ($v->isNull()) {
                $menu->getInventory()->setItem($k, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GRAY())->asItem()->setCustomName(" "));
            }
        }

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction): void {
            $player = $transaction->getPlayer();

            $rarity = match ($transaction->getAction()->getSlot()) {
                10 => RarityType::fromString("Simple"),
                11 => RarityType::fromString("Unique"),
                12 => RarityType::fromString("Elite"),
                13 => RarityType::fromString("Ultimate"),
                14 => RarityType::fromString("Legendary"),
                //15 => RarityType::fromString("Heroic"),
                default => null
            };
            if ($rarity == null) return;
            $rarityinv = new CERarityInventory($player, $rarity);
            $rarityinv->createInventory()->send($player);
        }));

        return $menu;
    }
}