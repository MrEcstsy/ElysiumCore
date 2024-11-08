<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\commands\spawn\SetSpawnSubCommand;

class SpawnCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", true));
        $this->registerSubCommand(new SetSpawnSubCommand($this->plugin, "set", "Set the world spawn in the block you are on", ["setspawn"]));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }
        
        if (empty($args)) {
            $sender->teleport($sender->getWorld()->getSpawnLocation());
            $sender->sendMessage(C::GREEN . "Teleported to world spawn.");
            return;
        }
        
        $targetName = $args["name"]; 
        if ($sender->hasPermission("command.admin")) {
            $target = Utils::getPlayerByPrefix($targetName);
            if ($target instanceof Player) {
                $target->teleport($target->getWorld()->getSpawnLocation());
                $sender->sendMessage(C::GREEN . "Teleported $targetName to world spawn.");
            } else {
                $sender->sendMessage(C::RED . "Player '$targetName' is not online.");
            }
        } else {
            $sender->sendMessage(C::RED . "You do not have permission to teleport others to spawn.");
        }
    }

    public function getPermission(): string {
        return "command.default";
    }
}