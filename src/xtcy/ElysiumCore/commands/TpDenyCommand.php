<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class TpDenyCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        if (!isset(ElysiumUtils::$tpaRequests[$sender->getName()])) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cYou have no pending teleport requests."));
            return;
        }

        $requesterName = ElysiumUtils::$tpaRequests[$sender->getName()]['requester'];
        $requester = Utils::getPlayerByPrefix($requesterName);

        if ($requester !== null && $requester->isOnline()) {
            $requester->sendMessage(C::colorize("&r&l&c(!) &r&cYou have been denied the teleport request from " . $sender->getName() . "."));
        }

        $sender->sendMessage(C::colorize("&r&l&c(!) &r&cYou have denied the teleport request from " . $requesterName . "."));
        unset(ElysiumUtils::$tpaRequests[$sender->getName()]);
        return;
    }
}