<?php

namespace xtcy\ElysiumCore\commands\exp;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;

class ExperienceCommand extends BaseCommand
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

        $exp = $sender->getXpManager()->getCurrentTotalXp();

        if (isset($args["name"])) {
            $target = Utils::getPlayerByPrefix($args["name"]);  
            if ($target !== null) {
                $sender->sendMessage(C::colorize("&r&l&5" . $target->getName() . "'s Experience: &r&f" . number_format($target->getXpManager()->getCurrentTotalXp()) . " XP &r&7(Level " . number_format($target->getXpManager()->getXpLevel()) . ")"));
            } else {
                $sender->sendMessage(C::colorize("&r&l&c(!) &r&cPlayer not found."));
            }
        } else {
            $sender->sendMessage(C::colorize("&r&l&dYour Experience: &r&f" . number_format($exp) . " XP &r&7(Level " . number_format($sender->getXpManager()->getXpLevel()). ")"));
        }
    }
}