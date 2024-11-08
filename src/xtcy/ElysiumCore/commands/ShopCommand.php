<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\Menu\ShopMenu;

class ShopCommand extends BaseCommand
{
    public function prepare(): void
    {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $session = Loader::getPlayerManager()->getSession($sender);

        if ($session !== null) {
            if ($session->getSetting("chest_inventories") === true || $session->getSetting("chest_inventories") === null) {
                ShopMenu::getShopCategoriesMenu($sender)->send($sender);
            } elseif ($session->getSetting("chest_inventories") === false) {
                $sender->sendForm(ShopMenu::getShopCategoriesForm($sender));
            }
        }
    }
}
    