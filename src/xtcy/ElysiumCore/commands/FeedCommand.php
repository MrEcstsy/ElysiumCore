<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;

class FeedCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "You must run this command in-game.");
            return;
        }

        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : $sender;

        if ($player !== null) {
            $session = Loader::getPlayerManager()->getSession($sender);
            if ($session->getCooldown("feed_command") === null || $session->getCooldown("feed_command") === 0) {
                $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
                $player->sendMessage(C::colorize("&r&6Your appetite has been sated."));
                $session->addCooldown("feed_command", 60);
            } else {
                $sender->sendMessage(C::colorize("&cYou must wait &6" . Utils::translateTime($session->getCooldown("feed_command")) . " &cseconds before you can use this command again."));
            }
        } elseif (($session = Loader::getPlayerManager()->getSession($sender)) !== null) {
            if ($session->getCooldown("feed_command") === null || $session->getCooldown("feed_command") === 0) {
                $sender->getHungerManager()->setFood($sender->getHungerManager()->getMaxFood());
                $sender->sendMessage(C::colorize("&r&6Your appetite has been sated."));
                $session->addCooldown("feed_command", 60);
            }
        }
    }

    public function getPermission(): string {
        return "command.feed";
    }
}