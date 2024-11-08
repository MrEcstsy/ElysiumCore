<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class TpaCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("command.default");

        $this->registerArgument(0, new RawStringArgument("name", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        $target = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;

        if ($target === null || !$target->isOnline()) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cThat player is not online."));
            return;
        }
        ElysiumUtils::$tpaRequests[$target->getName()] = [
            'requester' => $sender->getName(),
            'time' => time()
        ];
    
        $target->sendMessage(C::colorize("&r&l&a(!) &r&aYou have received a teleport request from " . $sender->getNameTag() . " &r&7(/tpa)"));
        $sender->sendMessage(C::colorize("&r&l&a(!) &r&aYou have sent a teleport request to " . $target->getNameTag()));

        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new class($this, $target->getName()) extends Task {
            private $targetName;

            public function __construct(string $targetName) {
                $this->targetName = $targetName;
            }
            
            public function onRun(): void {
                ElysiumUtils::checkRequestTimeout($this->targetName);
            }
        }, 12000);

        return;
    }
}