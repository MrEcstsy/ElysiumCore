<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use wockkinmycup\utilitycore\utils\Utils;

class FixCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("command.fix");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can only be used in-game.");
            return;
        }

        Utils::repairAllItems($sender);
    }
}