<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class TpAcceptCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        if (!isset(ElysiumUtils::$tpaRequests[$sender->getName()]) && !isset(ElysiumUtils::$tpahereRequests[$sender->getName()])) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cYou have no pending teleport requests."));
            return;
        }

        if (isset(ElysiumUtils::$tpaRequests[$sender->getName()])) {
            $requesterName = ElysiumUtils::$tpaRequests[$sender->getName()]['requester'];
            $requester = isset($requesterName) ? Utils::getPlayerByPrefix($requesterName) : null;

            if ($requester === null || !$requester->isOnline()) {
                $sender->sendMessage(C::colorize("&r&l&c(!) &r&cThat player is not online."));
                unset(ElysiumUtils::$tpaRequests[$sender->getName()]);
                return;
            }

            $requester->teleport($sender->getPosition());
            $sender->sendMessage(C::colorize("&r&l&a(!) &r&aYou have teleported " . $requester->getName() . " to your location."));
            $requester->sendMessage(C::colorize("&r&l&a(!) &r&aYou have been teleported to " . $sender->getName() . "."));
            unset(ElysiumUtils::$tpaRequests[$sender->getName()]);
        } elseif (isset(ElysiumUtils::$tpahereRequests[$sender->getName()])) {
            $requesterName = ElysiumUtils::$tpahereRequests[$sender->getName()]['requester'];
            $requester = isset($requesterName) ? Utils::getPlayerByPrefix($requesterName) : null;

            if ($requester === null || !$requester->isOnline()) {
                $sender->sendMessage(C::colorize("&r&l&c(!) &r&cThat player is not online."));
                unset(ElysiumUtils::$tpahereRequests[$sender->getName()]);
                return;
            }

            $sender->teleport($requester->getPosition());
            $sender->sendMessage(C::colorize("&r&l&a(!) &r&aYou have been teleported to " . $requester->getName() . "."));
            $requester->sendMessage(C::colorize("&r&l&a(!) &r&a" . $sender->getName() . " has accepted your teleport request."));
            unset(ElysiumUtils::$tpahereRequests[$sender->getName()]);
        }

        return;
    }
}
