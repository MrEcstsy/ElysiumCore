<?php

namespace xtcy\ElysiumCore\utils\Menu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\items\Items;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class EnchanterShop {

    public static function getEnchanterMenu(Player $player): InvMenu {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $inventory = $menu->getInventory();
        
        $menu->setName(C::colorize("&r&8Elysium Enchanter"));
        Utils::fillInventory($inventory, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK())->asItem(), [3, 4, 5, 10, 11, 12, 13, 14, 16, 22]);

        $items = [
            3 => VanillaItems::INK_SAC()->setCustomName(C::colorize("&r&l&fBlack Scroll"))->setLore([C::colorize("&r&7Click to receive &fBlack Scroll&r&7."), "", C::colorize("&r&l&bCOST: &r&f20,000")]),
            4 => VanillaItems::PAPER()->setCustomName(C::colorize("&r&l&eTransmog Scroll"))->setLore([C::colorize("&r&7Click to receive &eTransmog Scroll&r&7."), "", C::colorize("&r&l&bCOST: &r&f20,000")]),
            5 => StringToItemParser::getInstance()->parse("empty_map")->setCustomName(C::colorize("&r&eWhite scroll"))->setLore([C::colorize("&r&7Click to receive &eWhite Scroll&r&7."), "", C::colorize("&r&l&bCOST: &r&f20,000")]),
            10 => VanillaItems::BOOK()->setCustomName(C::colorize("&r&l&fSimple Enchantment Book &r&7(Right Click)"))->setLore([C::colorize("&r&7Click to receive &fSimple Enchantment Book&r&7."), "", C::colorize("&r&l&bCOST: &r&f600")]),
            11 => VanillaItems::BOOK()->setCustomName(C::colorize("&r&l&aUnique Enchantment Book &r&7(Right Click)"))->setLore([C::colorize("&r&7Click to receive &aUnique Enchantment Book&r&7."), "", C::colorize("&r&l&bCOST: &r&f1,200")]),
            12 => VanillaItems::BOOK()->setCustomName(C::colorize("&r&l&bElite Enchantment Book &r&7(Right Click)"))->setLore([C::colorize("&r&7Click to receive &bElite Enchantment Book&r&7."), "", C::colorize("&r&l&bCOST: &r&f2,400")]),
            13 => VanillaItems::BOOK()->setCustomName(C::colorize("&r&l&eUltimate Enchantment Book &r&7(Right Click)"))->setLore([C::colorize("&r&7Click to receive &eUltimate Enchantment Book&r&7."), "", C::colorize("&r&l&bCOST: &r&f6,600")]),
            14 => VanillaItems::BOOK()->setCustomName(C::colorize("&r&l&6Legendary Enchantment Book &r&7(Right Click)"))->setLore([C::colorize("&r&7Click to receive &6Legendary Enchantment Book&r&7."), "", C::colorize("&r&l&bCOST: &r&f30,000")]),
            16 => VanillaItems::BOOK()->setCustomName(Items::createRandomCEBook("generator", 1)->getName())->setLore([C::colorize("&r&7Click to receive &r&l&f➥ &r&3Enchantment Book &fGenerator&r&7."), "", C::colorize("&r&l&bCOST: &r&f15,000")]),
            22 => VanillaItems::EXPERIENCE_BOTTLE()->setCustomName(C::colorize("&r&l&5Your EXP"))->setLore([C::colorize("&r&7Welcome to the Elysium Enchanter&r&7."), "", C::colorize("&r&l&dEXP: &r" . number_format($player->getXpManager()->getCurrentTotalXp()))]),
        ];

        foreach ($items as $index => $item) {
            $inventory->setItem($index, $item);
        }

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction): void {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();

            if ($slot === 3) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::getEnchantScrolls("blackscroll", 1, mt_rand(50, 100)), 20000));
            } elseif ($slot === 4) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::getEnchantScrolls("transmog"), 20000));
            } elseif ($slot === 5) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::getEnchantScrolls("whitescroll"), 20000));
            } elseif ($slot === 10) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::createRandomCEBook("simple"), 600));
            } elseif ($slot === 11) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::createRandomCEBook("unique"), 1200));
            } elseif ($slot === 12) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::createRandomCEBook("elite"), 2400));
            } elseif ($slot === 13) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::createRandomCEBook("ultimate"), 6000));
            } elseif ($slot === 14) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::createRandomCEBook("legendary"), 30000));
            } elseif ($slot === 16) {
                $player->removeCurrentWindow();
                $player->sendForm(ElysiumUtils::sendEnchanterPurchaseConfirmation($player, Items::createRandomCEBook("generator"), 15000));
            }
        }));
        return $menu;
    }
}