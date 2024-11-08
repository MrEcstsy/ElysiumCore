<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use xtcy\ElysiumCore\Loader;

class RemoveHomeCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("command.default");

        $this->registerArgument(0, new RawStringArgument("home", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&cThis command can only be used in-game."));
            return;
        }

        $homeName = isset($args["home"]) ? $args["home"] : null;

        if ($homeName !== null) {
            $home = Loader::getHomeManager()->getPlayerHome($sender->getUniqueId(), $homeName);
            if ($home !== null) {
                Loader::getHomeManager()->deleteHome($home);
                $sender->sendMessage(C::colorize("&r&l&a(!) &r&aSuccessfully removed home &f" . $homeName . "&a."));
            } else {
                $sender->sendMessage(C::colorize("&r&l&c(!) &r&cCould not find home &e" . $homeName . "&c."));
            }
        } else {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cPlease specify a home name."));
        }
    }
}