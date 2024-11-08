<?php

namespace xtcy\ElysiumCore\commands\sell;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class SellHandSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        $item = $sender->getInventory()->getItemInHand();

        if (ElysiumUtils::isSellable($item)) {
            $price = ElysiumUtils::getSellPrice($item);
            var_dump("price" . $price);
            if ($price > 0) {
                $totalSellPrice = $price * $item->getCount();

                $session = Loader::getPlayerManager()->getSession($sender);
                $session->addBalance($totalSellPrice);
                $sender->sendMessage(C::colorize("&r&l&a(!) &r&aYou sold " . $item->getCount() . "x " . $item->getName() . " for $" . number_format($totalSellPrice)));
                $sender->getInventory()->setItemInHand(VanillaItems::AIR());
            } else {
                var_dump("price is below 0");
                $sender->sendMessage(C::colorize("&r&l&c(!) &r&cThe item in your hand cannot be sold."));
            }
        } else {
            var_dump("item is not sellable");
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cThe item in your hand cannot be sold."));
        }
    }
}