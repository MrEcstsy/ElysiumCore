<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class FlyCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        ElysiumUtils::toggleFlight($sender);
    }

    public function getPermission(): string {
        return "command.fly";
    }
}