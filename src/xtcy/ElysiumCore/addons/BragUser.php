<?php

namespace xtcy\ElysiumCore\addons\brag;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use xtcy\ElysiumCore\Loader;

class BragUser {
    
        /** @var Player */
	public Player $player;

    /** @var int */
	public int $time = 30;

    /**
     * @param Player $player
     */
	public function __construct(Player $player)
	{
		$this->player = $player;
	}

    /**
     * @param Player $player
     */
	public function createbragMenu(Player $player)
	{
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName("§r§8Viewing {$this->player->getName()}'s Inventory");
		foreach ($this->player->getInventory()->getContents(false) as $slot => $content) {
			$menu->getInventory()->setItem($slot, $content);
		}
		$menu->send($player);
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($player, $menu) {

			if($transaction->getItemClicked() !== null){
				$menu->onClose($player);
				Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use($player, $transaction, $menu): void{
					$player->getNetworkSession()->getInvManager()->onClientRemoveWindow($this->player->getNetworkSession()->getInvManager()->getCurrentWindowId());
				}), 10);
			}
		}));
	}

	public function update(): void{
		if(!Brag::isBragging($this->player)){
			Brag::destroyBrag($this->player);
			return;
		}
		if($this->player === null || !$this->player->isOnline() || $this->player->isClosed()){
			Brag::destroyBrag($this->player);
			return;
		}
		--$this->time;
		if($this->time <= 0){
			$this->player->sendMessage("§r§e§l(!) §r§eYour brag session has expired.");
			Brag::destroyBrag($this->player);
		}
	}
}