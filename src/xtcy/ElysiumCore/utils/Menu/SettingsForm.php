<?php

namespace xtcy\ElysiumCore\utils\Menu;

use pocketmine\player\Player;
use Vecnavium\FormsUI\SimpleForm;
use xtcy\ElysiumCore\Loader;
use pocketmine\utils\TextFormat as C;

class SettingsForm
{

    public static function getSettingsForm(): SimpleForm {
        $form = new SimpleForm(function(Player $player, $data): void {
            if ($data === null) return;

            $session = Loader::getPlayerManager()->getSession($player);
            if ($session !== null) {
                switch ($data) {
                    case 0:
                        self::toggleSetting($player, "chest_inventories");
                        break;
                    case 1:
                        self::toggleSetting($player, "announcer");
                        break;
                    case 2:
                        self::toggleSetting($player, "server_effects");
                        break;
                    case 3:
                        self::toggleSetting($player, "lootbox_broadcast");
                        break;    
                }
            }
        });

        $form->setTitle(C::colorize("&r&8Settings"));
        $form->setContent(C::colorize('&r&7Click to toggle server settings.'));
        $form->addButton("Chest Inventories");
        $form->addButton("Announcer");
        $form->addButton("Server Effects");
        $form->addButton("Lootbox Broadcast");

        return $form;
    }

    private static function toggleSetting(Player $player, string $settingName): void {
        $session = Loader::getPlayerManager()->getSession($player);
        if ($session !== null) {
            $currentValue = $session->getSetting($settingName);
            $newValue = !$currentValue;
            $session->setSetting($settingName, $newValue);

            $message = $newValue ? "&r&aEnabled" : "&r&4Disabled";
            $player->sendToastNotification(C::colorize("&r&l&e(&7!&e) SETTING CHANGED&r&7:"), C::colorize("&r&7'&f" . $settingName . "&r&7' has been " . $message . "&r&7."));
        }
    }
}
