<?php

namespace xtcy\ElysiumCore\commands\gems;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\Menu\GemMenu;

class GemCommand extends BaseCommand
{

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
        $this->setPermission("command.default");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $session = Loader::getPlayerManager()->getSession($sender);

        if ($session->getSetting("chest_inventories") === true || $session->getSetting("chest_inventories") === null) {
            GemMenu::getGemMenu($sender)->send($sender);
        } elseif ($session->getSetting("chest_inventories") === false) {
            $sender->sendForm(GemMenu::getGemForm($sender));
        }
    }
}