<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use xtcy\ElysiumCore\commands\sell\SellAllSubCommand;
use xtcy\ElysiumCore\commands\sell\SellHandSubCommand;

class SellCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerSubCommand(new SellAllSubCommand($this->plugin, "all", "Sell all sellable items in your inventory"));
        $this->registerSubCommand(new SellHandSubCommand($this->plugin, "hand", "Sell the item you are holding"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }
        
        $sender->sendMessage(C::colorize("&r&l&c(!) &r&cInvalid usage. Use /sell all or /sell hand"));
    }

    public function getPermission(): string
    {
        return "command.default";
    }
}