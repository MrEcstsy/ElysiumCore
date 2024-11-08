<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use xtcy\ElysiumCore\Loader;
use pocketmine\utils\TextFormat as C;

class HomesCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission("command.default");

        $this->registerArgument(0, new RawStringArgument("home", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }

        $homes = Loader::getHomeManager()->getHomeList($sender->getUniqueId());

        if (empty($homes)) {
            $sender->sendMessage(C::colorize("&cYou don't have any homes."));
            return;
        } else {
            $sender->sendMessage(C::colorize("&dYour homes:"));
            foreach ($homes as $home) {
                $phome = Loader::getHomeManager()->getPlayerHome($sender->getUniqueId(), $home->getName());
                $sender->sendMessage(C::colorize("&7- &d{$home->getName()}&7: &a{$phome->getWorld()->getFolderName()}&7, &a" .$phome->getPosition()->getX() . "&7, &a" . $phome->getPosition()->getY() . "&7, &a" . $phome->getPosition()->getZ()));
            }
        }

        $homeName = isset($args["home"]) ? $args["home"] : null;

        if ($homeName !== null) {
            $home = Loader::getHomeManager()->getPlayerHome($sender->getUniqueId(), $homeName);
            if ($home !== null) {
                $home->teleport($sender);
                $sender->sendMessage(C::colorize("&7Teleported to home '{$homeName}'."));
            } else {
                $sender->sendMessage(C::colorize("&cHome '{$homeName}' not found."));
            }
        } 
    }
}