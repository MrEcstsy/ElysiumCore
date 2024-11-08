<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use xtcy\ElysiumCore\Loader;

class ListWarpsCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("warp", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $warpManager = Loader::getWarpManager();
        $warps = $warpManager->getWarpList();

        if ($warps === null) {
            $sender->sendMessage(C::DARK_RED . "No Available Warps");
            return;
        }

        $warpNames = [];
        foreach ($warps as $warp) {
            $warpNames[] = $warp->getName();
        }

        $warpList = implode(C::WHITE . ", " . C::RED, $warpNames);
        $sender->sendMessage(C::GOLD . "Available Warps: " . C::RED . $warpList);
    }

    public function getPermission(): string {
        return "command.default";
    }
}