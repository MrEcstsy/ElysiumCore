<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\Menu\KitMenu;

class KitCommand extends BaseCommand
{

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $session = Loader::getPlayerManager()->getSession($sender);

        KitMenu::getKitCategoryMenu($sender)->send($sender);
    }
}