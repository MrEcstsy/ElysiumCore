<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;

class WarpCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("command.default");

        $this->registerArgument(0, new RawStringArgument("warp", false));
        $this->registerArgument(1, new RawStringArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $warpName = $args["warp"] ?? null;
        $targetPlayer = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : $sender;

        $warp = Loader::getWarpManager()->getWarp($warpName);

        if ($warp === null) {
            $sender->sendMessage(C::RED . "Warp '{$warpName}' does not exist.");
            return;
        }

        if ($targetPlayer === null) {
            $sender->sendMessage(C::RED . "Player '{$args["name"]}' not found.");
            return;
        }

        if ($targetPlayer !== $sender) {
            if (!$sender->hasPermission("command.warp.others")) {
                $sender->sendMessage(C::RED . "You do not have permission to warp others.");
                return;
            }

            $warp->teleport($targetPlayer);
            $sender->sendMessage(C::GREEN . "Player '{$targetPlayer->getName()}' has been warped to '{$warpName}'.");
        } else {
            $warp->teleport($sender);
            $sender->sendMessage(C::GREEN . "You have been warped to '{$warpName}'.");
        }
    }

    public function getPermission(): string {
        return "command.default";
    }
}
