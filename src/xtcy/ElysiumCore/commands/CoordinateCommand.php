<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\player\Player;

class CoordinateCommand extends BaseCommand
{

    public function prepare(): void
    {
        $this->setPermission("command.coordinate");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $pk = new GameRulesChangedPacket();
        $pk->gameRules = ["showCoordinates" => new BoolGameRule(true, true)];
        $sender->getNetworkSession()->sendDataPacket($pk);

    }
}