<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\items\Items;

class GiveMaxHomeCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", true));
        $this->registerArgument(1, new IntegerArgument("amount", true));
    }
    
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;
        $amount = isset($args["amount"]) ? $args["amount"] : 1;

        if ($player !== null) {
            if ($player->getInventory()->canAddItem(Items::giveMaxHome())) {
                $player->getInventory()->addItem(Items::giveMaxHome(1, $amount));
            } else {
                $player->getWorld()->dropItem($player->getLocation()->asVector3(), Items::giveMaxHome(1, $amount));
            }
        }
    }

    public function getPermission(): string
    {
        return "command.admin";
    }
}