<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use xtcy\ElysiumCore\utils\Menu\EnchanterShop;

class EnchanterCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        EnchanterShop::getEnchanterMenu($sender)->send($sender);
    }
}