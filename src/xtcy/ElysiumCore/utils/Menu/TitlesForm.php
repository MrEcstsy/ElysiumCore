<?php

namespace xtcy\ElysiumCore\utils\Menu;

use pocketmine\player\Player;
use Vecnavium\FormsUI\SimpleForm;
use xtcy\ElysiumCore\Loader;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;

class TitlesForm {

    public static array $titles = [
        ['title' => '§r§8 [§r§cVlone§8]', 'permission' => 'title.vlone'],
        ['title' => '§r§8 [§r§5GBGR§8]', 'permission' => 'title.gbgr'],
        ['title' => '§r§8 [§r§cEuphoria§8]', 'permission' => 'title.euphoria'],
        ['title' => '§r§8 [§r§cElysium§8]', 'permission' => 'title.elysium'],
        ['title' => '§r§8 [§r§cUrTrash§8]', 'permission' => 'title.urtrash'],
        ['title' => '§r§8 [§r§c$$$§8]', 'permission' => 'title.$$$'],
        ['title' => '§r§8 [§r§cP2W§8]', 'permission' => 'title.p2w'],
        ['title' => '§r§8 [§r§cPanda§8]', 'permission' => 'title.panda'],
        ['title' => '§r§8 [§r§cOP§8]', 'permission' => 'title.op'],
        ['title' => '§r§8 [§r§cGOD§8]', 'permission' => 'title.god'],
        ['title' => '§r§8 [§r§cSoulMaster§8]', 'permission' => 'title.soulmaster'],
        ['title' => '§r§8 [§r§cP2L§8]', 'permission' => 'title.p2l'],
        ['title' => '§r§8 [§r§ck§8]', 'permission' => 'title.k'],
        ['title' => "§r§8 [§r§cCartFein§8]", 'permission' => 'title.cartfein'],
        ['title' => "§r§8 [§r§cBlinker§8]", 'permission' => 'title.blinker'],
        ['title' => "§r§8 [§r§cStormcaller§8]", 'permission' => 'title.stormcaller'],
    ];

    public static function titleForm(Player $player, int $page = 1): SimpleForm {
        $session = Loader::getPlayerManager()->getSession($player);
        $form = new SimpleForm(function (Player $player, $data) use ($session, $page): void {
            if ($data === null) {
                return;
            }
            
            if ($data === 'next') {
                $player->sendForm(TitlesForm::titleForm($player, $page + 1));
            } elseif ($data === 'prev') {
                $player->sendForm(TitlesForm::titleForm($player, $page - 1));
            } else {
                $titlesPerPage = 5;
                $selectedTitleIndex = ($page - 1) * $titlesPerPage + $data;
                $selectedTitle = TitlesForm::$titles[$selectedTitleIndex]['title'];
                $player->sendMessage("Selected title: " . $selectedTitle);
                $session->setTitle($selectedTitle);
            }
        });

        $titlesPerPage = 5;
        $start = ($page - 1) * $titlesPerPage;
        $end = min($start + $titlesPerPage, count(TitlesForm::$titles));

        for ($i = $start; $i < $end; $i++) {
            $titleData = TitlesForm::$titles[$i];
            $status = Utils::getPermissionLockedStatus($player, $titleData['permission']);
            $form->addButton($titleData['title'] . "\n" . $status);
        }

        if ($page > 1) {
            $form->addButton('Previous Page', -1, '', 'prev');
        }

        if ($end < count(TitlesForm::$titles)) {
            $form->addButton('Next Page', -1, '', 'next');
        }

        $form->setTitle(C::colorize("&r&8Titles"));
        return $form;
    }
}
