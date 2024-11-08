<?php

namespace xtcy\ElysiumCore\addons\scorehud;

use Ifera\ScoreHud\event\TagsResolveEvent;
use pocketmine\event\Listener;
use xtcy\ElysiumCore\Loader;

class TagResolveListener implements Listener
{

    private Loader $plugin;

    public function __construct(Loader $plugin)
    {
            $this->plugin = $plugin;
    }

    public function onTagResolve(TagsResolveEvent $event)
    {
        $player = $event->getPlayer();
        $tag = $event->getTag();
        $tags = explode(".", $tag->getName(), 2);
        $value = "";

        if ($tags[0] !== "elysium" || count($tags) < 2) {
            return;
        }

        switch ($tags[1]) {
            case "balance":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getBalance();
                break;
            case "kills":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getKills();
                break;
            case "deaths":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getDeaths();
                break;
            case "gems":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getGems();
                break;
            case "level":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getLevel();
                break;
            case "xp":
                $value = $player->getXpManager()->getCurrentTotalXp();
                break;
            case "kdr":
                $value = $this->plugin->getPlayerManager()->getSession($player)->getKDRRatio(); 
                break;   
        }

        $tag->setValue((string) $value);
    }
}