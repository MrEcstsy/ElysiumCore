<?php

namespace xtcy\ElysiumCore\utils\Menu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use Vecnavium\FormsUI\CustomForm;
use Vecnavium\FormsUI\SimpleForm;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;

class GemMenu
{

    public static function getGemMenu(Player $player): InvMenu {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $inv = $menu->getInventory();
        $menu->setName(C::colorize("&r&8Gems"));
        $session = Loader::getPlayerManager()->getSession($player);
        Utils::fillInventory($inv, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK)->asItem(), [12, 14]);

        $inv->setItem(12, VanillaItems::EMERALD()->setCustomName(C::colorize("&r&l&aWithdraw Gems"))->setLore([
            C::colorize("&r&7Balance: &e" . number_format($session->getGems()) . " Gems"),
            "",
            C::colorize("&r&7Click to withdraw gems")
        ]));

        $inv->setItem(14, VanillaBlocks::CHEST()->asItem()->setCustomName(C::colorize("&r&l&aShop"))->setLore([
            C::colorize("&r&7Click to checkout all the items"),
            C::colorize("&r&7you can spend your gems on"),
        ]));


        $menu->setListener(InvMenu::readonly());

        return $menu;
    }

    public static function getGemForm(Player $player): SimpleForm {
        $form = new SimpleForm(function(Player $player, $data): void {
            if($data === null) return;

            $player->getInventory()->addItem($data);
            $player->sendMessage(C::colorize("&r&l&7[&a&lElysium&7] &r&7You have received a gem!"));
        });

        $session = Loader::getPlayerManager()->getSession($player);
        $form->setTitle(C::colorize("&r&8Gems"));
        $form->setContent(C::colorize("&r&7Check out all the items you can spend your gems on\n&r&7Balance: &e" . number_format($session->getGems()) . " Gems"));
        return $form;
    }
}