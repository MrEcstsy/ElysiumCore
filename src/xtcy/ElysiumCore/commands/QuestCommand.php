<?php 

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\Menu\QuestUI;

class QuestCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize("&r&cThis command can only be used in-game."));
            return;
        }

        $session = Loader::getPlayerManager()->getSession($sender);

        if ($session->getSetting("chest_inventories") === true || $session->getSetting("chest_inventories") === null) {
            QuestUI::getMainQuestInventory($sender)->send($sender);
        } elseif ($session->getSetting("chest_inventories") === false) {
            if ($session->getCooldown("quest_reset") === 0 || $session->getCooldown("quest_reset") === null) {
                if ($session->getAllQuests() !== null) {
                    foreach ($session->getAllQuests() as $quest => $data) {
                        if ($session->getQuestProgress($quest, 'progress') <= 0) {
                            $session->removeQuest($quest);
                        }
                    }
                }
            }

            $sender->sendForm(QuestUI::getMainQuestForm($sender));

        }
        
    }

    public function getPermission(): string
    {
        return "command.default";
    }
}