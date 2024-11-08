<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;

class BroadcastCommand extends BaseCommand {

    public function prepare(): void
    {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new TextArgument("message", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        Server::getInstance()->broadcastMessage(C::colorize($args["message"]));
    }

    public function getPermission(): string
    {
        return "command.admin";
    }
}