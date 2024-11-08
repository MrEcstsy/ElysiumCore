<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\Menu\SettingsForm;

class SettingsCommand extends BaseCommand
{

    /**
     * @inheritDoc
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
        $this->registerArgument(0, new RawStringArgument("setting", true));
        $this->registerArgument(1, new RawStringArgument("value", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $sender->sendForm(SettingsForm::getSettingsForm());
    }
}