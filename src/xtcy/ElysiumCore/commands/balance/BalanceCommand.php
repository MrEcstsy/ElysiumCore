<?php

namespace xtcy\ElysiumCore\commands\balance;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;

class BalanceCommand extends BaseCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
        $this->registerArgument(0, new RawStringArgument("name", true));
    }
    
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $session = Loader::getPlayerManager()->getSession($sender);

        if ($session !== null) {
            if (isset($args["name"])) {
                $target = Utils::getPlayerByPrefix($args["name"]);
                if ($target !== null) {
                    $session2 = Loader::getPlayerManager()->getSession($target);
                    if ($session2 !== null) {
                        $sender->sendMessage(C::colorize("&r&l&5" . $target->getName() . "'s &dBalance: &r&f$" . number_format($session2->getBalance())));
                    } else {
                        $sender->sendMessage(C::colorize("&r&l&cPlayer not found."));
                    }
                } else {
                    $sender->sendMessage(C::colorize("&r&l&cPlayer not found."));
                }
            } else {
                $sender->sendMessage(C::colorize("&r&l&dYour Balance: &r&f$" . number_format($session->getBalance())));
            }   
        }   
    }
}
