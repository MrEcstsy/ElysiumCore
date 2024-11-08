<?php

namespace xtcy\ElysiumCore\commands\balance;

use pocketmine\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

use CortexPE\Commando\args\IntegerArgument;
use xtcy\ElysiumCore\Loader;
use CortexPE\Commando\BaseCommand;
use xtcy\ElysiumCore\items\Items;

class WithdrawCommand extends BaseCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void {
        $this->setPermission("command.default");
        $this->registerArgument(0, new IntegerArgument("amount", false));
    }
    
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $session = Loader::getPlayerManager()->getSession($sender);

        if ($session !== null) {
            if (is_integer($args["amount"])) {
                if ($session->getBalance() >= $args["amount"]) {
                    $sender->getInventory()->addItem(Items::createBankNote($sender, $args["amount"]));
                    $session->subtractBalance($args["amount"]);
                    $sender->sendMessage(C::colorize("&r&l&a(!) &r&aSuccessfully withdrawn $" . number_format($args["amount"]) . " from your balance."));
                } else {
                    $sender->sendMessage(C::colorize("&r&l&c(!) &r&cInsufficient funds!"));
                }
            } elseif (!is_integer($args["amount"])) {
                $sender->sendMessage(C::colorize("&r&l&c(!) &r&cInvalid amount!"));
            }
        }
    }
}