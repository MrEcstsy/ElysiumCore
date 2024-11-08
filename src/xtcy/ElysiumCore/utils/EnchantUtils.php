<?php

namespace xtcy\ElysiumCore\utils;

use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\player\Player;
use xtcy\ElysiumCore\enchants\tools\HasteEnchantment;

class EnchantUtils
{

    public const TOOL_TO_ITEMFLAG = [
		Pickaxe::class => ItemFlags::PICKAXE,
		Sword::class => ItemFlags::SWORD,
		Axe::class => ItemFlags::AXE,
		Hoe::class => ItemFlags::HOE,
		Shovel::class => ItemFlags::SHOVEL,
		Bow::class => ItemFlags::BOW,
	];

    public const ARMOR_SLOT_TO_ITEMFLAG = [
		ArmorInventory::SLOT_HEAD => ItemFlags::HEAD,
		ArmorInventory::SLOT_CHEST => ItemFlags::TORSO,
		ArmorInventory::SLOT_LEGS => ItemFlags::LEGS,
		ArmorInventory::SLOT_FEET => ItemFlags::FEET,
	];

	public static function armorSlotToType(int $slot): string{
		return match ($slot) {
			ArmorInventory::SLOT_HEAD => "helmet",
			ArmorInventory::SLOT_CHEST => "chestplate",
			ArmorInventory::SLOT_LEGS => "leggings",
			ArmorInventory::SLOT_FEET => "boots",
			default => "undefined"
		};
	}

	public static function getToolItemFlag(Tool $item): int {
		foreach(self::TOOL_TO_ITEMFLAG as $class => $itemFlag) {
			if($item instanceof $class) return $itemFlag;
		}
		throw new \UnexpectedValueException("Unknown item type " . get_class($item));
	}

    public static function hasHasteEnchantment(Item $item): bool
    {
        $enchantments = $item->getEnchantments();
        foreach ($enchantments as $enchantmentInstance) {
            if ($enchantmentInstance->getType() instanceof HasteEnchantment) {
                return true;
            }
        }
        return false;
    }

    public static function getHasteLevel(Item $item): int
    {
        $enchantments = $item->getEnchantments();
        foreach ($enchantments as $enchantmentInstance) {
            $enchantment = $enchantmentInstance->getType();
            if ($enchantment instanceof HasteEnchantment) {
                return $enchantmentInstance->getLevel();
            }
        }
        return 0;
    }

    public static function hasConfusionEnchantment(Item $item): bool
    {
        $enchantments = $item->getEnchantments();
        foreach ($enchantments as $enchantmentInstance) {
            if ($enchantmentInstance->getType() instanceof HasteEnchantment) {
                return true;
            }
        }
        return false;
    }

    public static function getConfusionLevel(Item $item): int
    {
        $enchantments = $item->getEnchantments();
        foreach ($enchantments as $enchantmentInstance) {
            $enchantment = $enchantmentInstance->getType();
            if ($enchantment instanceof HasteEnchantment) {
                return $enchantmentInstance->getLevel();
            }
        }
        return 0;
    }

    public static function calculateTotalEnchantmentLevel(Player $player, Enchantment $enchantment): int {
        $totalLevel = 0;
        $armorInventory = $player->getArmorInventory();

        foreach ($armorInventory->getContents() as $item) {
            $enchantments = $item->getEnchantments();
            foreach ($enchantments as $enchantmentInstance) {
                if ($enchantmentInstance->getType() instanceof $enchantment) {
                    $totalLevel += $enchantmentInstance->getLevel();
                }
            }
        }

        return $totalLevel;
    }

    public static function translateRarityToColor(int $rarity): string
    {
        switch ($rarity) {
            case 1:
                return "§7";
            case 2:
                return "§a";
            case 3:
                return "§b";
            case 4:
                return "§e";
            case 5:
                return "§6";
            case 6:
                return "§4";
            default:
                return "§7";
        }
    }
}