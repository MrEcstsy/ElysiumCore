<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use MakayaYoel\Slotbot\menus\SlotbotMainMenu;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SlotBotCommand extends BaseCommand {

    public function prepare(): void
    {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        new SlotbotMainMenu($sender);
    }
}