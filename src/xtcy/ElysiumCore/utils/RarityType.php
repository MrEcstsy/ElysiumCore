<?php

namespace xtcy\ElysiumCore\utils;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\EnumTrait;
use xtcy\ElysiumCore\enchants\util\CERarity;
use xtcy\ElysiumCore\enchants\util\CustomEnchantments;

/**
     * This doc-block is generated automatically, do not modify it manually.
     * This must be regenerated whenever registry members are added, removed or changed.
     * @see build/generate-registry-annotations.php
     * @generate-registry-docblock
     *
     * @method static RarityType SIMPLE()
     * @method static RarityType UNIQUE()
     * @method static RarityType ELITE()
     * @method static RarityType ULTIMATE()
     * @method static RarityType LEGENDARY()
     * @method static RarityType MASTERY()
     *
     */
class RarityType {
    use EnumTrait {
        __construct as Enum___construct;
    }

    protected static function setup() : void{
        self::registerAll(
            new self("simple", "Simple", CERarity::SIMPLE, "§7Simple", "§7"),
            new self("unique", "Unique", CERarity::UNIQUE, "§aUnique", "§a"),
            new self("elite", "Elite", CERarity::ELITE, "§bElite", "§b"),
            new self("ultimate", "Ultimate", CERarity::ULTIMATE, "§eUltimate", "§e"),
            new self("legendary", "Legendary", CERarity::LEGENDARY, "§6Legendary", "§6"),
            new self("mastery", "Mastery", CERarity::MASTERY, "§4Mastery", "§4"),
        );
    }

    private function __construct(
        string                  $enumName,
        private readonly string $displayName,
        private readonly int    $id,
        private readonly string $customName,
        private readonly string $color,
    ){
        $this->Enum___construct($enumName);
    }

    public static function fromString(string $name) : self {
        $result = self::_registryFromString($name);
        assert($result instanceof self);
        return $result;
    }

    public static function fromId(int $id) : self {
        $result = self::_registryGetAll();

        foreach($result as $r) {
            if($r->getId() == $id) {
                assert($r instanceof self);
                return $r;
            }
        }

        return self::SIMPLE();
    }

    public function getDisplayName() : string {
        return $this->displayName;
    }

    public function getId() : int{
        return $this->id;
    }

    public function getCustomName() : string {
        return $this->customName;
    }

    public function getColor() : string {
        return $this->color;
    }

    public function giveRandomEnchantBook(Item $item, Player $player, bool $agreement = true, int $success = 100, int $destroy = 50): void {
        $randomEnchant = [];

        foreach (CustomEnchantments::getAll() as $enchantment) {
            if($enchantment->getRarity() == $this->getId()) {
                $randomEnchant[] = $enchantment;
            }
        }
        $enchant = $randomEnchant[array_rand($randomEnchant)];
        $message = "§l§e(!) §r§7The {$this->getCustomName()} §r§7Thebook gave you.. ";

        $level = mt_rand(1, $enchant->getMaxLevel());
        $e = new EnchantmentInstance($enchant, $level);

        $enchantBook = VanillaItems::ENCHANTED_BOOK();

        if ($agreement) {
            $en = $enchant->getLoreLine($level);
            $player->sendMessage($message . $en);
        }
        $player->getInventory()->canAddItem($enchantBook) ? $player->getInventory()->addItem($enchantBook) : $player->getWorld()->dropItem($player->getPosition()->asVector3(), $enchantBook);
    }

}