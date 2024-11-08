<?php

namespace xtcy\ElysiumCore\utils\Menu;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuType;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use xtcy\ElysiumCore\enchants\util\CustomEnchantment;
use xtcy\ElysiumCore\enchants\util\CustomEnchantments;
use xtcy\ElysiumCore\utils\ElysiumUtils;
use xtcy\ElysiumCore\utils\RarityType;

class CERarityInventory
{

    public readonly InvMenuType $type;
    private static RarityType $rarityType;

    public static array $cachedSlot;

    /**
     * @param Player $player
     * @param RarityType $rarityType
     */
    public function __construct(public Player $player, RarityType $rarityType) {
        self::$rarityType = $rarityType;
    }

    public static  function getRarityType() : RarityType {
        return self::$rarityType;
    }

    public function createInventory(): InvMenu {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);

        $menu->setName(self::getRarityType()->getCustomName() . " Enchants");
        $items = [];
        self::$cachedSlot = [];
        $slot = 0;
        foreach(CustomEnchantments::getAllForRarity(self::getRarityType()) as $enchantId) {
            $enchant = EnchantmentIdMap::getInstance()->fromId($enchantId);
            $book = VanillaItems::BOOK()->setLore(["", "§7Click to view info about this enchant"]);
            $book->setCustomName($enchant->getName() . RarityType::fromId($enchant->getRarity())->getColor());
            ElysiumUtils::applyDisplayEnchant($book);
            $items[] = $book;
            self::$cachedSlot[$slot] = $enchant;
            $slot++;
        }
        $menu->getInventory()->setContents($items);
        $menu->getInventory()->setItem(26, VanillaItems::ARROW()->setCustomName("§7Back"));

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) : void {
            $player = $transaction->getPlayer();

            if($transaction->getItemClicked()->getTypeId() == VanillaItems::ARROW()->getTypeId()) {
                new CEInventory($transaction->getPlayer());
            } else {
                /**
                 * @var CustomEnchantment $enchant
                 */
                $enchant = self::$cachedSlot[$transaction->getAction()->getSlot()];
                $player->removeCurrentWindow();
                //$player->sendForm($enchant->getForm());
            }
        }));

        return $menu;
    }
}