<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\addons\brag\Brag;

class SeeBragCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument('name', false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }   

        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;

        if ($player === null || !$player->isOnline()) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cThat player is not online."));
            return;
        }

        if (!Brag::isBragging($player)) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cThat player has not bragged recently."));
            return;
        }

        Brag::setBragging($player)->createbragMenu($player);
    }

    public function getPermission(): string {
        return "command.default";
    }
}