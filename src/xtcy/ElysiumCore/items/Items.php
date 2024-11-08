<?php

namespace xtcy\ElysiumCore\items;

use PgSql\Lob;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\enchants\util\CustomEnchantment;
use xtcy\ElysiumCore\utils\EnchantUtils;

class Items {

    public static function getEnchantScrolls(string $type, int $amount = 1, int $percentage = 100): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($type)) {
            case "blackscroll":
                $item = VanillaItems::INK_SAC()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&fBlack Scroll"));
                $item->setLore([
                    C::colorize("&r&7Removes a random enchantment"),
                    C::colorize("&r&7from an item and converts"),
                    C::colorize("&r&7it into a &f" . $percentage . "% &r&7success book"),
                    C::colorize("&r&fPlace scroll onto item to extract")
                ]);

                $item->getNamedTag()->setString("scrolls", "blackscroll");
                $item->getNamedTag()->setInt("black_scroll", $percentage);
                break;
            case "transmog":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&eTransmog Scroll"));
                $item->setLore([
                    C::colorize("&r&7Organizes enchants by &erarity &7on item"),
                    C::colorize("&r&7and adds the &dlore &bcount &7to name"),
                    C::colorize(""),
                    C::colorize("&r&e&oPlace scroll on item to apply.")
                ]);

                $item->getNamedTag()->setString("scrolls", "transmog");
                break;
            case "whitescroll":
                $item = StringToItemParser::getInstance()->parse("empty_map")->setCount($amount);

                $item->setCustomName(C::colorize("&r&eWhite Scroll"));
                $item->setLore([
                    C::colorize("&r&7Prevents an item from being destroyed"),
                    C::colorize("&r&7due to a failed Enchantment Book."),
                    C::colorize("&r&ePlace scroll on item to apply."),
                ]);

                $item->getNamedTag()->setString("scrolls", "whitescroll");
                break;
            case "itemrename":
                $item = VanillaItems::NAME_TAG()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&6Item Renametag &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Rename and customize your equipment."),
                ]);

                $item->getNamedTag()->setString("scrolls", "itemrename");
                break;
            case "lorecrystal":
                $item = VanillaItems::DYE()->setColor(DyeColor::RED())->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&6Item Lore Crystal &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Apply a custom line of lore"),
                    C::colorize("&r&7to customize your equipment."),
                    "",
                    C::colorize("&r&l&6* &r&7Limited to 1 custom line of lore per item.")
                ]);

                $item->getNamedTag()->setString("scrolls", "lorecrystal");
                break;
            case "playerkillcounter":
                $item = VanillaItems::MAGMA_CREAM()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&6Player Kill Counter"));

                $item->setLore([
                    C::colorize("&r&7Drag n' drop this onto a weapon"),
                    C::colorize("&r&7to track your kill count on players."),
                ]);

                $item->getNamedTag()->setString("scrolls", "killcounter");
                break;
        }
        return $item;
    }

    public static function createEnchantmentBook(Enchantment $enchantment, int $level = 1, int $successchance = 100, int $destroychance = 100): ?Item {
        $item = VanillaItems::ENCHANTED_BOOK();
    
        $einstance = new EnchantmentInstance($enchantment, $level);
        $item->setCustomName(C::colorize("&r&l" . EnchantUtils::translateRarityToColor($enchantment->getRarity()) . $enchantment->getName() . " " .  Utils::getRomanNumeral($einstance->getLevel())));
   
        if ($enchantment instanceof CustomEnchantment) {
            $item->setLore([
                C::colorize("&r&a" . $successchance . "% Success Rate"),
                C::colorize("&r&c" . $destroychance . "% Destroy Rate"),
                C::colorize("&r&e" . $enchantment->getDescription()), 
                C::colorize("&r&7Drag n' Drop onto item to enchant.")
            ]);
        } else {
            $item->setLore([
                C::colorize("&r&7Default lore for non-CustomEnchantment"),
            ]);
        }

        $item->getNamedTag()->setString("enchant_book", strtolower($enchantment->getName()));
        $item->getNamedTag()->setInt("level", $level);
        $item->getNamedTag()->setInt("successrate", $successchance);
        $item->getNamedTag()->setInt("destroyrate", $destroychance);
        return $item;
    }

    public static function getCrateKey(string $type, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($type)) {
            case "vote":
                $item = VanillaBlocks::TRIPWIRE_HOOK()->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&dVote Crate &r&7(/vote) Key"));
                $item->setLore([
                    C::colorize("&r&7Go to &f/warp crates &7to use your &l&dVote Crate Key")
                ]);

                $item->getNamedTag()->setString("crate_key", "vote");
                break;
            case "cipher":
                $item = VanillaBlocks::TRIPWIRE_HOOK()->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&fCipher Crate &r&7(Tier 1) Key"));
                $item->setLore([
                    C::colorize("&r&7Go to &f/warp crates &7to use your &l&fCipher Crate Key")
                ]);

                $item->getNamedTag()->setString("crate_key", "cipher");
                break;
            case "zenith":
                $item = VanillaBlocks::TRIPWIRE_HOOK()->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&9Zenith Crate &r&7(Tier 2) Key"));
                $item->setLore([
                    C::colorize("&r&7Go to &f/warp crates &7to use your &l&9Zenith Crate Key")
                ]);

                $item->getNamedTag()->setString("crate_key", "zenith");
                break;
            case "empyrean":
                $item = VanillaBlocks::TRIPWIRE_HOOK()->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&cEmpyrean Crate &r&7(Tier 3) Key"));
                $item->setLore([
                    C::colorize("&r&7Go to &f/warp crates &7to use your &l&cEmpyrean Crate Key")
                ]);

                $item->getNamedTag()->setString("crate_key", "empyrean");
                break;
        }

        return $item;
    }

    public static function createBankNote(?Player $player = null, int $amount = 1): Item {
        $item = VanillaItems::PAPER();

        if ($player === null) {
            $signer = "Console";
        } else {
            $signer = $player->getName();
        }

        $item->setCustomName(C::colorize("&r&l&bBank Note &r&7(Right Click)"));
        $item->setLore([
            C::colorize("&r&dValue: &f$" . number_format($amount)),
            C::colorize("&r&dSigner: &f" . $signer)
        ]);

        $item->getNamedTag()->setString("bank_note", "true");
        $item->getNamedTag()->setInt("bank_note", $amount);
        return $item;
    }

    public static function createExperienceBottle(?Player $player = null, int $amount = 1): Item {
        $item = VanillaItems::EXPERIENCE_BOTTLE();

        if ($player === null) {
            $signer = "Console";
        } else {
            $signer = $player->getName();
        }

        $item->setCustomName(C::colorize("&r&l&aExperience Bottle &r&7(Right Click)"));
        $item->setLore([
            C::colorize("&r&dValue: &f" . number_format($amount)),
            C::colorize("&r&dSigner: &f" . $signer)
        ]);

        $item->getNamedTag()->setString("experience_bottle", "true");
        $item->getNamedTag()->setInt("experience_bottle", $amount);
        return $item;
    }

    public static function createRandomCEBook(string $rarity, int $amount = 1): Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($rarity)) {
            case "simple":
                $item = VanillaItems::BOOK()->setCount($amount);    

                $item->setCustomName(C::colorize("&r&l&fSimple Enchantment Book &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Examine to receive a random"),
                    C::colorize("&r&fsimple &7enchantment book")
                ]);

                $item->getNamedTag()->setString("random_book", "simple");
                break;
            case "unique":
                $item = VanillaItems::BOOK()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&aUnique Enchantment Book &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Examine to receive a random"),
                    C::colorize("&r&aunique &7enchantment book")
                ]);

                $item->getNamedTag()->setString("random_book", "unique");
                break;
            case "elite":
                $item = VanillaItems::BOOK()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&bElite Enchantment Book &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Examine to receive a random"),
                    C::colorize("&r&belite &7enchantment book")
                ]);

                $item->getNamedTag()->setString("random_book", "elite");
                break;
            case "ultimate":
                $item = VanillaItems::BOOK()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&eUltimate Enchantment Book &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Examine to receive a random"),
                    C::colorize("&r&eultimate &7enchantment book")
                ]);

                $item->getNamedTag()->setString("random_book", "ultimate");
                break;
            case "legendary":
                $item = VanillaItems::BOOK()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&6Legendary Enchantment Book &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Examine to receive a random"),
                    C::colorize("&r&6legendary &7enchantment book")
                ]);

                $item->getNamedTag()->setString("random_book", "legendary");
                break;
            case "heroic":
                $item = VanillaItems::BOOK()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&dHeroic Enchantment Book &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Examine to receive a random"),
                    C::colorize("&r&dheroic &7enchantment book")
                ]);

                $item->getNamedTag()->setString("random_book", "heroic");
                break;
            case "generator":
                $item = VanillaItems::BOOK()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&f➥ &r&3Enchantment Book &fGenerator &r&7(Right Click)"));
                $item->setLore([
                    C::colorize("&r&7Right-Click (in your hand) to receive"),
                    C::colorize("&r&7one of the books listed below"),
                    "",
                    C::colorize("&r&l&fRandom Loot (&r&71 Items&r&f&l)"),
                    C::colorize("&r&l&f * 16x Simple Enchantment Book &r&7(Right Click)"),
                    C::colorize("&r&l&f * 8x &aUnique Enchantment Book &r&7(Right Click)"),
                    C::colorize("&r&l&f * 4x &bElite Enchantment Book &r&7(Right Click)"),
                    C::colorize("&r&l&f * 2x &eUltimate Enchantment Book &r&7(Right Click)"),
                    C::colorize("&r&l&f * 1x &6Legendary Enchantment Book &r&7(Right Click)"),
                ]);

                $item->getNamedTag()->setString("random_book", "generator");
                break;
        }
        return $item;
    }

    public static function createEnchantFragment(string $type, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);
        switch (strtolower($type)) {
            case "unbreaking":
                $item = VanillaItems::IRON_INGOT()->setCount($amount);
                $item->setCustomName("§r§l§bEnchantment Fragment [§r§dUnbreaking V§l§b]");
                $item->setLore([
                    "§r§7Drag n' Drop on a pair of",
                    "§r§b'§7armor§b'§7 to enchant §dUnbreaking V§7."
                ]);

                $item->getNamedTag()->setString("enchantmentfragment", "unbreakingv");
                break;
            case "thorns":
                $item = VanillaItems::REDSTONE_DUST()->setCount($amount);
                $item->setCustomName("§r§l§cEnchantment Fragment [§r§7Thorns III§l§c]");
                $item->setLore([
                    "§r§7Drag n' Drop on a pair of",
                    "§r§c'§7armor§c'§7 to enchant §cThorns III§7."
                ]);
                $item->getNamedTag()->setString("enchantmentfragment", "thornsiii");
                break;
            case "depth_strider":
                $item = VanillaItems::LAPIS_LAZULI()->setCount($amount);
                $item->setCustomName("§r§l§bEnchantment Fragment [§r§7Depth Strider III§l§b]");
                $item->setLore([
                   "§r§7Drag n' Drop on a pair of",
                   "§r§b'§7armor§b'§7 to enchant §bDepth Strider III§7."
                ]);

                $item->getNamedTag()->setString("enchantmentfragment", "depthstrideriii");
                break;
            case "looting":
                $item = VanillaItems::GOLD_INGOT()->setCount($amount);
                $item->setCustomName("§r§l§bEnchantment Fragment [§r§dLooting V§l§b]");
                $item->setLore([
                    "§r§7Drag n' Drop on a pair of",
                    "§r§b'§7weapon§b'§7 to enchant §dLooting V§7."
                ]);
                $item->getNamedTag()->setString("enchantmentfragment", "lootingv");
                break;
            case "fortune":
                $item = VanillaItems::NETHER_QUARTZ()->setCount($amount);
                
                $item->setCustomName("§r§l§bEnchantment Fragment [§r§eFortune V§l§b]");
                $item->setLore([
                    "§r§7Drag n' Drop on a pair of",
                    "§r§b'§7pickaxe§b'§7 to enchant §eFortune V§7."
                ]);
                $item->getNamedTag()->setString("enchantmentfragment", "fortunev");
                break;
            case "fire_aspect":
                $item = VanillaItems::BLAZE_POWDER()->setCount($amount);

                $item->setCustomName("§r§l§bEnchantment Fragment [§r§fFire Aspect III§l§b]");
                $item->setLore([
                    "§r§7Drag n' Drop on a pair of",
                    "§r§b'§7weapon§b'§7 to enchant §fFire Aspect III§7."
                ]);
                $item->getNamedTag()->setString("enchantmentfragment", "fireaspectiii");
                break;
            default:
                $item = VanillaItems::AIR();
                echo "invalid type";
                break;    
        }
        return $item;
    }

    public static function giveMaxHome(int $amount = 1, int $increment = 1): ?Item {
        $item = VanillaBlocks::BED()->setColor(DyeColor::RED)->asItem()->setCount($amount);

        $item->setCustomName(C::colorize("&r&l&eMax Home Increase &r&7(Right Click)"));
        $item->setLore([
            C::colorize("&r&7Adds +" . $increment . " &e/home &7slots to your player.")
        ]);

        $item->getNamedTag()->setInt("max_home", $increment);
        return $item;
    }

    public static function createBossEgg(string $boss, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($boss)) {
            case "broodmother":
                $item = StringToItemParser::getInstance()->parse("spider_spawn_egg")->setCount($amount);

                $item->setCustomName(C::colorize("&r&5&lBrood Mother"));

                $item->setLore([
                    C::colorize('&r&7This monster egg contains a'),
                    C::colorize('&r&cdangerous&r &7warzone boss'),
                    ' ',
                    C::colorize('&r&5&lLORE'),
                    C::colorize('&r&7&oThe mother of all spiders, with the'),
                    C::colorize('&r&7&omost deadly venom in all the'),
                    C::colorize('&r&7&oServomerse. No one has survived a'),
                    C::colorize('&r&7&obite from the Brood Mother.'),
                    ' ',
                    C::colorize('&r&5&lDIFFICULTY'),
                    C::colorize('&r&5Ultimate')
                ]);

                $item->getNamedTag()->setString("boss", "broodmother");
                break;
            case "ancientguardian":
                $item = StringToItemParser::getInstance()->parse("iron_golem_spawn_egg")->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&6&lAncient Guardian"));
                
                $item->setLore([
                    C::colorize('&r&7This monster egg contains an'),
                    C::colorize('&r&6immense&r &7warzone boss'),
                    ' ',
                    C::colorize('&r&6&lLORE'),
                    C::colorize('&r&7&oThe Ancient Guardian, a relic from'),
                    C::colorize('&r&7&othe depths of time. Its power is'),
                    C::colorize('&r&7&ounmatched and its rage unending.'),
                    ' ',
                    C::colorize('&r&6&lDIFFICULTY'),
                    C::colorize('&r&6Legendary')
                ]);
                
                $item->getNamedTag()->setString("boss", "ancientguardian");
                break;
            case "blazefury":
                $item = StringToItemParser::getInstance()->parse("blaze_spawn_egg")->setCount($amount);

                $item->setCustomName(C::colorize("&r&e&lBlaze Fury"));

                $item->setLore([
                    C::colorize('&r&7This monster egg contains a'),
                    C::colorize('&r&einfernal&r &7warzone boss'),
                    ' ',
                    C::colorize('&r&e&lLORE'),
                    C::colorize('&r&7&oThe embodiment of fire and fury,'),
                    C::colorize('&r&7&oBlaze Fury incinerates all who'),
                    C::colorize('&r&7&odare to challenge it.'),
                    ' ',
                    C::colorize('&r&e&lDIFFICULTY'),
                    C::colorize('&r&eUltimate')
                ]);

                $item->getNamedTag()->setString("boss", "blazefury");
                break;    
        }

        return $item;
    }

    public static function createRankVoucher(string $rank, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($rank)) {
            case "seeker":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&eRANK "&r&fSeeker&r&l&e"'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to redeem the Seeker Rank"),
                    "",
                    C::colorize("&r&l&eDuration: &r&cPermanent"),
                ]);

                $item->getNamedTag()->setString("rank_voucher", "seeker");
                break;
            case "luminary":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&eRANK "&r&fLuminary&r&l&e"'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to redeem the Luminary Rank"),
                    "",
                    C::colorize("&r&l&eDuration: &r&cPermanent"),
                ]);

                $item->getNamedTag()->setString("rank_voucher", "luminary");
                break;
            case "celestial":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&eRANK "&r&fCelestial&r&l&e"'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to redeem the Celestial Rank"),
                    "",
                    C::colorize("&r&l&eDuration: &r&cPermanent"),
                ]);

                $item->getNamedTag()->setString("rank_voucher", "celestial");
                break;
            case "elsyian":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&eRANK "&r&fElsyian&r&l&e"'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to redeem the Elsyian Rank"),
                    "",
                    C::colorize("&r&l&eDuration: &r&cPermanent"),
                ]);

                $item->getNamedTag()->setString("rank_voucher", "elsyian");
                break;
            case "ascendant":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&eRANK "&r&fAscendant&r&l&e"'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to redeem the Ascendant Rank"),
                    "",
                    C::colorize("&r&l&eDuration: &r&cPermanent"),
                ]);

                $item->getNamedTag()->setString("rank_voucher", "ascendant");
                break;

        }
        return $item;
    }

    public static function createPerkVoucher(string $perk, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($perk)) {
            case "randomizer":
                $item = VanillaItems::FEATHER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&5Perk &fRandomizer'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to receive one"),
                    C::colorize("&r&7of the perks listed below"),
                    "",
                    C::colorize("&r&l&fRandom Loot (&r&71 Item&r&l&f)"),
                    C::colorize(" &r&l&f* 1x &fPerk: &5Fix All"),
                    C::colorize(" &r&l&f* 1x &fPerk: &5Fly"),
                    C::colorize(" &r&l&f* 1x &fPerk: &5Near"),
                    C::colorize(" &r&l&f* 1x &fPerk: &5Heal"),
                    C::colorize(" &r&l&f* 1x &fPerk: &5Bless"),
                ]);

                $item->getNamedTag()->setString("perk_voucher", "randomizer");
                break;
            case "fixall":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&fPerk: &5Fix All'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to unlock the Fix All Perk."),
                ]);

                $item->getNamedTag()->setString("perk_voucher", "fixall");
                break;
            case "fly":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&fPerk: &5Fly'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to unlock the Fly Perk."),
                ]);

                $item->getNamedTag()->setString("perk_voucher", "fly");
                break;
            case "near":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&fPerk: &5Near'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to unlock the Near Perk."),
                ]);

                $item->getNamedTag()->setString("perk_voucher", "near");
                break;
            case "heal":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&fPerk: &5Heal'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to unlock the Heal Perk."),
                ]);

                $item->getNamedTag()->setString("perk_voucher", "heal");
                break;
            case "bless":
                $item = VanillaItems::PAPER()->setCount($amount);

                $item->setCustomName(C::colorize('&r&l&fPerk: &5Bless'));

                $item->setLore([
                    C::colorize("&r&7Right-Click to unlock the Bless Perk."),
                ]);

                $item->getNamedTag()->setString("perk_voucher", "bless");
                break;
        }
        return $item;
    }

    public static function createTitleVoucher(string $type, int $amount = 1): ?Item {
        $item = VanillaItems::NAME_TAG()->setCount($amount);


        $item->setCustomName(C::colorize("&r&f➥ &l&5TITLE '&r&f" . $type ."&r&5&l' &r&7(Right Click)"));

        $item->setLore([
            C::colorize("&r&7Right-Click to redeem the " . $type . " Title."),
        ]);

        $item->getNamedTag()->setString("title_voucher", $type);

        return $item;
    }

    public static function createLootbox(string $type, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);
        $openLore = C::colorize("&r&7Right-Click (in your hand) to receive") . "\n" . C::colorize("&r&7some of the rewards listed below") . "\n";
        switch (strtolower($type)) {
            case "stormcaller":
                $item = VanillaBlocks::BEACON()->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&f➥ Lootbox: &bStormcaller &f<&e⭐⭐⭐&f⭐⭐>"));

                $item->setLore([
                    $openLore,
                    C::colorize("&r&l&fRandom Loot (&r&74 Items&l&f)"),
                    C::colorize("&r&l&f * 1x &5Brood Mother"),
                    C::colorize("&r&l&f * 1x &l&eUltimate XP Pouch &r&7(Right Click)"),
                    C::colorize("&r&l&f * 3x " . self::getCrateKey("zenith")->getName()),
                    C::colorize("&r&l&f * 4x " . self::getCrateKey("cipher")->getName()),
                    C::colorize("&r&l&f * 1x " . self::getEnchantScrolls("whitescroll")->getName()),
                    C::colorize("&r&l&f * 1x " . self::getEnchantScrolls("transmog")->getName()),
                    C::colorize("&r&l&f * 1x " . self::getEnchantScrolls("blackscroll")->getName()),
                    C::colorize("&r&l&f * 1x " . self::getEnchantScrolls("lorecrystal")->getName()),
                    C::colorize("&r&l&f * 1x " . self::createRandomCEBook("legendary")->getName()),
                    C::colorize("&r&l&f * 1x " . self::createEnchantFragment("fire_aspect")->getName()),
                    C::colorize("&r&l&f * 1x " . self::createPerkVoucher("generator")->getName()),
                    C::colorize("&r&l&fBonus Loot (&r&71 Item&r&l&f)"), 
                    C::colorize("&r&l&f * 1x &l&5TITLE '&r&fStormcaller&l&5' &r&7(Right Click)"),
                ]);

                $item->getNamedTag()->setString("lootbox", "stormcaller");
                break;
                
        }
        return $item;
    }

    public static function createIncursionSummoner(string $type, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($type)) {
            case "soul":
                $item = VanillaItems::NETHER_STAR()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&f➥ Incursion: &4Soul &r&7(Right Click)"));

                $item->setLore([
                    C::colorize("&r&7&o Harness the forbidden energies of the Nether"),
                    C::colorize("&r&7&o and call forth the relentless Soul Guardians,"),
                    C::colorize("&r&7&o bent on consuming the realm in darkness."),
                    "",
                    C::colorize("&r&7Right-Click (in your hand) to unleash"),
                    C::colorize("&r&7a Soul Guardian incursion upon the land."),
                ]);                

                $item->getNamedTag()->setString("incursion", "soul");

                break;
            case "hollow":
                $item = VanillaItems::PHANTOM_MEMBRANE()->setCount($amount);    

                $item->setCustomName(C::colorize("&r&l&f➥ Incursion: &fHollow &r&7(Right Click)"));

                $item->setLore([
                    C::colorize("&r&7&o Unleash the menacing Hollow Guardians,"),
                    C::colorize("&r&7&o ancient protectors of the underworld,"),
                    C::colorize("&r&7&o sworn to reclaim their dominion from the living."),
                    "",
                    C::colorize("&r&7Right-Click (in your hand) to summon"),
                    C::colorize("&r&7a Hollow Guardian incursion."),
                ]);
                
                $item->getNamedTag()->setString("incursion", "hollow");

                break;
            case "astral":
                $item = VanillaItems::ARROW()->setCount($amount);  

                $item->setCustomName(C::colorize("&r&l&f➥ Incursion: &dAstral &r&7(Right Click)"));
                
                $item->setLore([
                    C::colorize("&r&7&o Harness the mystical power of the stars"),
                    C::colorize("&r&7&o and beckon the ethereal Astral Rangers,"),
                    C::colorize("&r&7&o guardians of celestial balance and order,"),
                    C::colorize("&r&7&o to defend their realm from mortal interference."),
                    "",
                    C::colorize("&r&7Right-Click (in your hand) to summon"),
                    C::colorize("&r&7an Astral Ranger incursion."),
                ]);

                $item->getNamedTag()->setString("incursion", "astral");
                break;
        }

        return $item;
    }

    public static function createDropPackage(string $type, int $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($type)) {
            case "simple":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);

                $item->setCustomName(C::colorize("&r&l&fSimple Elysium Chest &r&7(Right Click)"));

                $item->setLore([
                    C::colorize("&r&7A cache of equipment packed by"),
                    C::colorize("&r&7the Intergalactic Ethereal Station."),
                    "",
                    C::colorize("&r&7Contains &f5 Simple Rarity &7items..."),
                    C::colorize("&r&l&f* &r&7Vanilla Equipment"),
                    C::colorize("&r&l&f* &r&7Vanilla Potions"),
                    C::colorize("&r&l&f* &r&7Sheep Spawners"),
                    C::colorize("&r&l&f* &r&7Food"),
                    C::colorize("&r&l&f* &r&7Buckets"),
                    C::colorize("&r&l&f* &r&7$10,000 Money Notes"),
                    C::colorize("&r&l&f* &r&71,00XP Bottles"),
                    C::colorize("&r&l&f* &r&7Simple Enchantment Book"),
                    C::colorize("&r&l&f* &r&7Tier #1 Rank"),
                ]);

                $item->getNamedTag()->setString("drop_package", "simple");
                break;
            case "unique":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&aUnique Elysium Chest &r&7(Right Click)"));

                $item->setLore([
                    C::colorize("&r&7A cache of equipment packed by"),
                    C::colorize("&r&7the Intergalactic Ethereal Station."),
                    "",
                    C::colorize("&r&7Contains &a5 Unique Rarity &7items..."),
                    C::colorize("&r&l&a* &r&7Unique Equipment"),
                    C::colorize("&r&l&a* &r&7Pig/Chicken Spawners"),
                    C::colorize("&r&l&a* &r&7Hoppers"),
                    C::colorize("&r&l&a* &r&7$50,000 Money Notes"),
                    C::colorize("&r&l&a* &r&72,00-5,000XP Bottles"),
                    C::colorize("&r&l&a* &r&7Unique Player Titles"),
                    C::colorize("&r&l&a* &r&7Unique Enchantment Book"),
                    C::colorize("&r&l&a* &r&7Unique Secret Dust"),
                    C::colorize("&r&l&a* &r&7Player Kill Tracker"),
                    C::colorize("&r&l&a* &r&7Tier #2 Rank"),
                    C::colorize("&r&a... and more!")
                ]);

                $item->getNamedTag()->setString("drop_package", "unique");
                break;
            case "elite":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&bElite Elysium Chest &r&7(Right Click)"));

                $item->setLore([
                    C::colorize("&r&7A cache of equipment packed by"),
                    C::colorize("&r&7the Intergalactic Ethereal Station."),
                    "",
                    C::colorize("&r&7Contains &b5 Elite Rarity &7items..."),
                    C::colorize("&r&l&b* &r&7Elite Equipment"),
                    C::colorize("&r&l&b* &r&7Creeper Eggs"),
                    C::colorize("&r&l&b* &r&7Zombie, Skeleton Spawners"),
                    C::colorize("&r&l&b* &r&7Spider, Wolf Spawners"),
                    C::colorize("&r&l&b* &r&7Hoppers"),
                    C::colorize("&r&l&b* &r&7$100,000 Money Notes"),
                    C::colorize("&r&l&b* &r&75,00-20,000XP Bottles"),
                    C::colorize("&r&l&b* &r&7Elite Player Titles"),
                    C::colorize("&r&l&b* &r&7Elite Enchantment Book"),
                    C::colorize("&r&l&b* &r&7Elite Secret Dust"),
                    C::colorize("&r&l&b* &r&7Tier #3 Rank"),
                    C::colorize("&r&b... and more!")
                ]);

                $item->getNamedTag()->setString("drop_package", "elite");
                break;
            case "ultimate":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&eUltimate Elysium Chest &r&7(Right Click)"));

                $item->setLore([
                    C::colorize("&r&7A cache of equipment packed by"),
                    C::colorize("&r&7the Intergalactic Ethereal Station."),
                    "",
                    C::colorize("&r&7Contains &e5 Ultimate Rarity &7items..."),
                    C::colorize("&r&l&e* &r&7Ultimate Equipment"),
                    C::colorize("&r&l&e* &r&7Ultimate /gkit Items"),
                    C::colorize("&r&l&e* &r&7Ultimate Boss Eggs"),
                    C::colorize("&r&l&e* &r&7Hoppers"),
                    C::colorize("&r&l&e* &r&7$250,000 Money Notes"),
                    C::colorize("&r&l&e* &r&25,000-75,000XP Bottles"),
                    C::colorize("&r&l&e* &r&7Item Nametags"),
                    C::colorize("&r&l&e* &r&7Transmog Scrolls"),
                    C::colorize("&r&l&e* &r&7White & 50% Black Scrolls"),
                    C::colorize("&r&l&e* &r&7Blaze & IG Spawners"),
                    C::colorize("&r&l&e* &r&7Mystery Mob Spawners"),
                    C::colorize("&r&l&e* &r&7Tier #4 Rank"),
                    C::colorize("&r&e... and more!")
                ]);

                $item->getNamedTag()->setString("drop_package", "ultimate");
                break;
            case "legendary":
                $item = VanillaBlocks::CHEST()->asItem()->setCount($amount);
                
                $item->setCustomName(C::colorize("&r&l&6Legendary Elysium Chest &r&7(Right Click)"));

                $item->setLore([
                    C::colorize("&r&7A cache of equipment packed by"),
                    C::colorize("&r&7the Intergalactic Ethereal Station."),
                    "",
                    C::colorize("&r&7Contains &f5 Legendary Rarity &7items..."),
                    C::colorize("&r&l&6* &r&7Legendary Equipment"),
                    C::colorize("&r&l&6* &r&7Legendary /gkit Items"),
                    C::colorize("&r&l&6* &r&7Legendary Boss Eggs"),
                    C::colorize("&r&l&6* &r&7$1,000,000 Money Notes"),
                    C::colorize("&r&l&6* &r&50,000-100,000XP Bottles"),
                    C::colorize("&r&l&6* &r&7White & 75% Black Scrolls"),
                    C::colorize("&r&l&6* &r&7Nether Mob Spawners"),
                    C::colorize("&r&l&6* &r&7Legendary Player Titles"),
                    C::colorize("&r&l&6* &r&7Mystery Mob Spawners"),
                    C::colorize("&r&l&6* &r&710 Slot Enchantment Orbs"),
                    C::colorize("&r&l&6* &r&7Home Increaser"),
                    C::colorize("&r&l&6* &r&7Tier #5 Rank"),
                    C::colorize("&r&6... and more!")
                ]);

                $item->getNamedTag()->setString("drop_package", "legendary");
                break;
            }

        return $item;
    }
}