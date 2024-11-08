<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\items\Items;

class GiveVoteKeyCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", true));
    }
    
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;

        if ($player !== null) {
            if ($player->getInventory()->canAddItem(Items::getCrateKey("vote"))) {
                $player->getInventory()->addItem(Items::getCrateKey("vote"));
            } else {
                $player->getWorld()->dropItem($player->getLocation()->asVector3(), Items::getCrateKey("vote"));
            }
        }
    }

    public function getPermission(): string
    {
        return "command.admin";
    }
}