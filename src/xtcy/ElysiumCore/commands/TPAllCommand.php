<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class TPAllCommand extends BaseCommand
{

    public function prepare(): void
    {
        $this->setPermission("command.admin");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $players = Server::getInstance()->getOnlinePlayers();

        foreach ($players as $player) {
            $player->teleport($sender->getPosition());
        }
    }
}