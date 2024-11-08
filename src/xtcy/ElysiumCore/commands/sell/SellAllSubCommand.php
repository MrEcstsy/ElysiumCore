<?php

namespace xtcy\ElysiumCore\commands\sell;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class SellAllSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission("command.sellall");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        $totalSellPrice = 0;
        $inventory = $sender->getInventory();
        
        foreach ($inventory->getContents() as $slot => $item) {
            if (ElysiumUtils::isSellable($item)) {
                $price = ElysiumUtils::getSellPrice($item);
                if ($price > 0) {
                    $totalSellPrice += $price * $item->getCount();
                    $inventory->removeItem($item);
                }
            }
        }

        if ($totalSellPrice > 0) {
            $session = Loader::getPlayerManager()->getSession($sender);
            $session->addBalance($totalSellPrice);
            $sender->sendMessage(C::colorize("&r&l&a(!) &r&aYou sold all sellable items for $" . number_format($totalSellPrice)));
        } else {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cYou have no sellable items in your inventory."));
        }
    }
}
