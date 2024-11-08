<?php

namespace xtcy\ElysiumCore\commands\balance;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;

class TakeBalanceCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = isset($args["name"]) ? Utils::getPlayerByPrefix($args["name"]) : null;
        $amount = isset($args["amount"]) ? $args["amount"] : null;

        if ($player !== null) {
            $session = Loader::getPlayerManager()->getSession($player);

            if ($session !== null) {
                if ($amount !== null) {
                    $session->subtractBalance($amount);
                    $sender->sendMessage(C::colorize("&r&a$" . number_format($amount) . " has been taken from " . $player->getName() . "'s balance."));
                }
            }
        }
    }

    public function getPermission(): string
    {
        return "command.admin";
    }
}