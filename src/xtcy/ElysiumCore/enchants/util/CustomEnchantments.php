<?php

namespace xtcy\ElysiumCore\enchants\util;

use muqsit\simplepackethandler\SimplePacketHandler;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\EventPriority;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\utils\RegistryTrait;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\enchants\all\ReforgedEnchantment;
use xtcy\ElysiumCore\enchants\armor\AngelicEnchantment;
use xtcy\ElysiumCore\enchants\armor\ArmoredEnchantment;
use xtcy\ElysiumCore\enchants\armor\boots\AntiGravityEnchantment;
use xtcy\ElysiumCore\enchants\armor\boots\AscendedEnchantment;
use xtcy\ElysiumCore\enchants\armor\boots\BounceEnchantment;
use xtcy\ElysiumCore\enchants\armor\boots\GearsEnchantment;
use xtcy\ElysiumCore\enchants\armor\boots\RocketEscapeEnchantment;
use xtcy\ElysiumCore\enchants\armor\CactusEnchantment;
use xtcy\ElysiumCore\enchants\armor\chestplate\MirroredEnchantment;
use xtcy\ElysiumCore\enchants\armor\CurseEnchantment;
use xtcy\ElysiumCore\enchants\armor\DiminishEnchantment;
use xtcy\ElysiumCore\enchants\armor\FrostbiteEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\AquaticEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\ClarityEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\DrunkEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\EndershiftEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\GlowingEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\HeavyEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\TrappedEnchantment;
use xtcy\ElysiumCore\enchants\armor\OverloadEnchantment;
use xtcy\ElysiumCore\enchants\armor\PoisonedEnchantment;
use xtcy\ElysiumCore\enchants\armor\TankEnchantment;
use xtcy\ElysiumCore\enchants\armor\ValorEnchantment;
use xtcy\ElysiumCore\enchants\armor\leggings\JellyEnchantment;
use xtcy\ElysiumCore\enchants\armor\leggings\RedeemerEnchantment;
use xtcy\ElysiumCore\enchants\tools\AutoSmeltEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\BleedEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\ConfusionEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\DemonicFinisherEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\FearEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\HolyEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\PummelEnchantment;
use xtcy\ElysiumCore\enchants\tools\ExperienceEnchantment;
use xtcy\ElysiumCore\enchants\tools\HasteEnchantment;
use xtcy\ElysiumCore\enchants\tools\ObsidianDestroyerEnchantment;
use xtcy\ElysiumCore\enchants\tools\SatansTreatEnchantment;
use xtcy\ElysiumCore\enchants\weapon\BlindEnchantment;
use xtcy\ElysiumCore\enchants\weapon\DecapitationEnchantment;
use xtcy\ElysiumCore\enchants\weapon\DoubleStrikeEnchantment;
use xtcy\ElysiumCore\enchants\weapon\ExecuteEnchantment;
use xtcy\ElysiumCore\enchants\weapon\FeatherweightEnchantment;
use xtcy\ElysiumCore\enchants\weapon\InquisitiveEnchantment;
use xtcy\ElysiumCore\enchants\weapon\InsomniaEnchantment;
use xtcy\ElysiumCore\enchants\weapon\RageEnchantment;
use xtcy\ElysiumCore\enchants\weapon\SilenceEnchantment;
use xtcy\ElysiumCore\enchants\weapon\SlownessEnchantment;
use xtcy\ElysiumCore\enchants\weapon\VampireEnchantment;
use xtcy\ElysiumCore\enchants\weapon\VampiricDevourEnchantment;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\RarityType;

/**
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 * @method static CustomEnchantment HASTE()
 * @method static ExperienceEnchantment EXPERIENCE()
 */
final class CustomEnchantments {
    use RegistryTrait;

    //Used for name to id conversion.
    public static array $ids = [];
    //Used to find all enchants by rarity.
    public static array $rarities = [];

    public function __construct() {
        self::setup();
    }

    protected static function setup() : void {
        SimplePacketHandler::createInterceptor(Loader::getInstance(), EventPriority::HIGH)
            ->interceptOutgoing(function(InventoryContentPacket $pk, NetworkSession $destination): bool {
                foreach ($pk->items as $i => $item) {
                    $pk->items[$i] = new ItemStackWrapper($item->getStackId(), self::display($item->getItemStack()));
                }
                return true;
            })
            ->interceptOutgoing(function(InventorySlotPacket $pk, NetworkSession $destination): bool {
                $pk->item = new ItemStackWrapper($pk->item->getStackId(), self::display($pk->item->getItemStack()));
                return true;
            })
            ->interceptOutgoing(function(InventoryTransactionPacket $pk, NetworkSession $destination): bool {
                $transaction = $pk->trData;

                foreach ($transaction->getActions() as $action) {
                    $action->oldItem = new ItemStackWrapper($action->oldItem->getStackId(), self::filter($action->oldItem->getItemStack()));
                    $action->newItem = new ItemStackWrapper($action->newItem->getStackId(), self::filter($action->newItem->getItemStack()));
                }
                return true;
            });
        EnchantmentIdMap::getInstance()->register(CustomEnchantmentIds::FAKE_ENCH_ID, new Enchantment("", -1, 1, ItemFlags::ALL, ItemFlags::NONE));

        self::registerSimple();
        self::registerUnique();
        self::registerElite();
        self::registerUltimate();
        self::registerLegendary();
        self::registerMastery();
    }

    protected static function registerSimple() : void {
        self::register(
            "AUTOSMELT",
            CustomEnchantmentIds::AUTOSMELT,
            new AutoSmeltEnchantment(
                "Auto Smelt", CERarity::SIMPLE, "Automatically smelt ores to ingots",
                1, ItemFlags::PICKAXE, ItemFlags::NONE,
            )
        );

        self::register(
            "CONFUSION",
            CustomEnchantmentIds::CONFUSION,
            new ConfusionEnchantment(
                "Confusion", CERarity::SIMPLE, "A chance to deal nausea to your victim",
                3, ItemFlags::AXE, ItemFlags::NONE,
            )
        );

        self::register(
            "INSOMNIA",
            CustomEnchantmentIds::INSOMNIA,
            new InsomniaEnchantment(
                "Insomnia", CERarity::SIMPLE, "Gives slowness, slow swinging and confusion",
                7, ItemFlags::SWORD, ItemFlags::NONE,
            )
        );

        self::register(
            "DECAPITATION",
            CustomEnchantmentIds::DECAPITATION,
            new DecapitationEnchantment(
                "Decapitation", CERarity::SIMPLE, "A chance to drop your victims head",
                10, ItemFlags::SWORD, ItemFlags::NONE,
            )
            );
    }

    protected static function registerUnique() : void {

        self::register(
            "HASTE",
            CustomEnchantmentIds::HASTE,
            new HasteEnchantment(
                "Haste", CERarity::UNIQUE, "Allows you to swing tools faster",
                4, ItemFlags::PICKAXE | ItemFlags::SHOVEL | ItemFlags::AXE, ItemFlags::NONE,
            )
        );

        self::register(
            "FEATHERWEIGHT",
            CustomEnchantmentIds::FEATHERWEIGHT,
            new FeatherweightEnchantment(
                "Featherweight", CERarity::UNIQUE, "A chance to give a burst of haste.",
                3, ItemFlags::SWORD, ItemFlags::NONE,
            )
        );

        self::register(
            "OBSIDIAN_DESTROYER",
            CustomEnchantmentIds::OBSIDIAN_DESTROYER,
            new ObsidianDestroyerEnchantment(
                "Obsidian Destroyer", CERarity::UNIQUE, "A chance to instantly break obsidian",
                5, ItemFlags::PICKAXE, ItemFlags::NONE,
            )
        );

        self::register(
            "ENDERSHIFT",
            CustomEnchantmentIds::ENDERSHIFT,
            new EnderShiftEnchantment(
                "Ender Shift", CERarity::UNIQUE, "Gives a speed/health boost at low hp.",
                3, ItemFlags::HEAD, ItemFlags::NONE,
            )
        );

        self::register(
            "BLAZED",
            CustomEnchantmentIds::BLAZED,
            new AquaticEnchantment(
                "Blazed", CERarity::UNIQUE, "Chance to set your attacker on fire",
                1, ItemFlags::TORSO, ItemFlags::NONE,
            )
        );

        self::register(
            "JELLYLEGS",
            CustomEnchantmentIds::JELLYLEGS,
            new JellyEnchantment(
                "Jelly Legs", CERarity::UNIQUE, "Chance to negate all fall damage",
                3, ItemFlags::LEGS, ItemFlags::NONE,
            )
        );

        self::register(
            'CURSE',
            CustomEnchantmentIds::CURSE,
            new CurseEnchantment(
                "Curse", CERarity::UNIQUE, "Chance to give your enemy mining fatigue",
                3, ItemFlags::ARMOR, ItemFlags::NONE,
            )
        );
    }

    protected static function registerElite() : void {
        self::register(
            "BOUNCE",
            CustomEnchantmentIds::BOUNCE,
            new BounceEnchantment(
                "Bounce", CERarity::ELITE, "Gives permanent jump boost",
                3, Itemflags::FEET, ItemFlags::NONE,
            )
        );

        self::register(
            "FEAR",
            CustomEnchantmentIds::FEAR,
            new FearEnchantment(
                "Fear", CERarity::ELITE, "Gives your opponent weakness",
                5, ItemFlags::AXE, ItemFlags::NONE,
            )
        );

        self::register(
            "FROSTBITE",
            CustomEnchantmentIds::FROSTBITE,
            new FrostbiteEnchantment(
                "Frostbite", CERarity::ELITE, "Chance to cause slowness to your attacker",
                5, ItemFlags::ARMOR, ItemFlags::NONE,
            )
        );

        self::register(
            "GLOWING",
            CustomEnchantmentIds::GLOWING,
            new GlowingEnchantment(
                "Glowing", CERarity::ELITE, "Gives permanent night vision",
                1, ItemFlags::HEAD, ItemFlags::NONE,
            )
        );

        self::register(
            "AQUATIC",
            CustomEnchantmentIds::AQUATIC,
            new AquaticEnchantment(
                "Aquatic", CERarity::ELITE, "Gives permanent water breathing",
                1, ItemFlags::HEAD, ItemFlags::NONE,
            )
        );

        self::register(
            "EXECUTE",
            CustomEnchantmentIds::EXECUTE,
            new ExecuteEnchantment(
                "Execute", CERarity::ELITE, "A (Level * 4)% Chance to deal massive damage on enemy players with less than 45% HP.",
                5, ItemFlags::SWORD, ItemFlags::NONE,
            )
        );
        self::register(
            "SLOWNESS",
            CustomEnchantmentIds::SLOWNESS,
            new SlownessEnchantment(
                "Trap", CERarity::ELITE, "Chance to apply slowness to enemy",
                3, ItemFlags::SWORD, ItemFlags::NONE,
            )
        );
        self::register(
            "PUMMEL",
            CustomEnchantmentIds::PUMMEL,
            new PummelEnchantment(
                "Pummel", CERarity::ELITE, "Chance to slow nearby enemy players\nfor a short period.",
                3, ItemFlags::AXE, ItemFlags::NONE,
            )
        );
        self::register(
            "POISONED",
            CustomEnchantmentIds::POISONED,
            new PoisonedEnchantment(
                "Poisoned", CERarity::ELITE, "Chance to give poison to your\nattacker.",
                4, ItemFlags::ARMOR, ItemFlags::NONE,
            )
        );
        self::register(
            "ANTIGRAVITY",
            CustomEnchantmentIds::ANTIGRAVITY,
            new AntiGravityEnchantment(
                "Anti Gravity", CERarity::ELITE, "Super jump but does not negate fall\ndamage.",
                3, Itemflags::FEET, ItemFlags::NONE,
            )
        );
        self::register(
            "BLIND",
            CustomEnchantmentIds::BLIND,
            new BlindEnchantment(
                "Blind", CERarity::ELITE, "A chance of causing blindness when\nattacking.",
                3, ItemFlags::SWORD, ItemFlags::NONE,
            )
        );
        self::register(
            "CACTUS",
            CustomEnchantmentIds::CACTUS,
            new CactusEnchantment(
                "Cactus", CERarity::ELITE, "Injure your attacker but does not\naffect your durability.",
                2, ItemFlags::ARMOR, ItemFlags::NONE,   
            )
        );
    }

    protected static function registerUltimate(): void {
        self::register(
            "DOUBLESTRIKE",
            CustomEnchantmentIds::DOUBLESTRIKE,
            new DoubleStrikeEnchantment(
                "Double Strike", CERarity::ULTIMATE, "Chance to strike twice",
                3, ItemFlags::SWORD, ItemFlags::NONE
            )
        );

        self::register(
            "DIMINISH",
            CustomEnchantmentIds::DIMINISH,
            new DiminishEnchantment(
                "Diminish", CERarity::ULTIMATE, "Chance to deal extra durability damage to all\n enemy armor with every attack", 
                4, ItemFlags::ARMOR, ItemFlags::NONE
            )
        );

        self::register(
            "CLEAVE",
            CustomEnchantmentIds::CLEAVE,
            new DiminishEnchantment(
                "Cleave", CERarity::ULTIMATE, "Damages players within a radius that increases with the\n level of enchant", 
                4, ItemFlags::AXE, ItemFlags::NONE
            )
        );

        self::register(
            "LAVABOUND",
            CustomEnchantmentIds::LAVABOUND,
            new DiminishEnchantment(
                "Lavabound", CERarity::ULTIMATE, "Permanent fire resistance", 
                1, ItemFlags::LEGS, ItemFlags::NONE
            )
        );

        self::register(
            "Holy",
            CustomEnchantmentIds::HOLY,
            new HolyEnchantment(
                "Holy", CERarity::ULTIMATE, "Chance to remove debuffs", 
                5, ItemFlags::AXE, ItemFlags::NONE
            )
        );

        self::register(
            "ANGELIC",
            CustomEnchantmentIds::ANGELIC,
            new AngelicEnchantment(
                "Angelic", CERarity::ULTIMATE, "Heals health over time whenever damaged, this\nenchantment IS stackable in terms of activation\nchance; however you can only have 1 active healing\ntask from Angelic at any given time",
                5, ItemFlags::ARMOR, ItemFlags::NONE
            )
        );

        self::register(
            "TANK",
            CustomEnchantmentIds::TANK,
            new TankEnchantment(
                "Tank", CERarity::ULTIMATE, "Decreases damage from enemy axe by 1.85% per level\nthis enchantment is stackable",
                4, ItemFlags::ARMOR, ItemFlags::NONE
            )
        );

        self::register(
            "VALOR",
            CustomEnchantmentIds::VALOR,
            new ValorEnchantment(
                "Valor", CERarity::ULTIMATE, "Reduces incoming damage while wielding a sword by\nup to 22.5% this enchantment is stackable",
                5, ItemFlags::ARMOR, ItemFlags::NONE
            )
        );

        self::register(
            "GEARS",
            CustomEnchantmentIds::GEARS,
            new GearsEnchantment(
                "Rocket Boots", CERarity::ULTIMATE, "Permanent Speed boost",
                3, ItemFlags::FEET, ItemFlags::NONE
            )
        );

        self::register(
            "CLARITY",
            CustomEnchantmentIds::CLARITY,
            new ClarityEnchantment(
                "Clarity", CERarity::ULTIMATE, "Cure blindness when attacked",
                3, ItemFlags::HEAD, ItemFlags::NONE
            )
        );
    }

    protected static function registerLegendary() : void {
        self::register(
            "REFORGED",
            CustomEnchantmentIds::REFORGED,
            new ReforgedEnchantment(
                "Reforged", CERarity::LEGENDARY, "Upon an item breaking, it has chance (10% up to 100%)\nof item to be fully repaired but at the cost of 1 enchant from your item",
                10, ItemFlags::ALL, ItemFlags::NONE
            )
        );

        self::register(
            "RAGE",
            CustomEnchantmentIds::RAGE,
            new RageEnchantment(
                "Rage", CERarity::LEGENDARY, 'Deals extra combo damage up to x5 damage when you are not attacked',
                5, ItemFlags::SWORD, ItemFlags::NONE
            )
        );

        self::register(
            "VAMPIRE",
            CustomEnchantmentIds::VAMPIRE,
            new VampireEnchantment(
                "Vampire", CERarity::LEGENDARY, "A chance to heal you for up to 3hp a\nfew seconds after you strike.",
                3, ItemFlags::SWORD, ItemFlags::NONE,   
            )
        );

        self::register(
            "BLEED",
            CustomEnchantmentIds::BLEED,
            new BleedEnchantment(
                "Bleed", CERarity::LEGENDARY, 'Chance to give your opponent the bleed affect',
                5, ItemFlags::AXE, ItemFlags::NONE
            )
        );

        self::register(
            "SILENCE",
            CustomEnchantmentIds::SILENCE,
            new SilenceEnchantment(
                "Silence", CERarity::LEGENDARY, 'Negate all enemy buffs upto 5 seconds',
                5, ItemFlags::SWORD, ItemFlags::NONE
            )
        );

        self::register(
            "INQUISITIVE",
            CustomEnchantmentIds::INQUISITIVE,
            new InquisitiveEnchantment(
                "Inquisitive", CERarity::LEGENDARY, "Increases the amount of vanilla XP gained from killing mobs.",
                4, ItemFlags::SWORD, ItemFlags::NONE
            )
        );

        self::register(
        "DRUNK",
        CustomEnchantmentIds::DRUNK,
        new DrunkEnchantment(
            "Drunk", CERarity::LEGENDARY, "Slowness and slow swinging with a chance to give buffed strength.",
            4, ItemFlags::HEAD, ItemFlags::NONE
        ));
        self::register(
            "OVERLOAD",
            CustomEnchantmentIds::OVERLOAD,
            new OverloadEnchantment(
                "Overload", CERarity::LEGENDARY, "Permanent increase of hearts.",
                3, ItemFlags::TORSO, ItemFlags::NONE
            )
        );
        self::register(
            "ARMORED",
            CustomEnchantmentIds::ARMORED,
            new ArmoredEnchantment(
                "Armored", CERarity::LEGENDARY, "Decreases damage from enemy swords by 1.85% per level\nthis enchantment is stackable",
                4, ItemFlags::ARMOR, ItemFlags::NONE
            )
        );

        self::register(
            "HEAVY",
            CustomEnchantmentIds::HEAVY,
            new HeavyEnchantment(
                "Heavy", CERarity::LEGENDARY, "Decreases damage from enemy bows by\n2% per level, this enchantment is\nstackable",
                5, ItemFlags::HEAD, ItemFlags::NONE
            )
        );
    }

    protected static function registerMastery(): void {
        self::register(
            "VAMPIRICDEVOUR",
            CustomEnchantmentIds::VAMPIRICDEVOUR,
            new VampiricDevourEnchantment(
                "Vampiric Devour", CERarity::MASTERY, "Small chance to regain all health while attacking",
                1, ItemFlags::SWORD, ItemFlags::NONE
            )
        );

        self::register(
            "DEMONICFINISHER",
            CustomEnchantmentIds::DEMONICFINISHER,
            new DemonicFinisherEnchantment(
                "Demonic Finisher", CERarity::MASTERY, "Chance to deal up to 5 hearts of damage when\nyour opponent is below 4 hearts",
                5, ItemFlags::AXE, ItemFlags::NONE
            )
        );

        self::register(
            "MIRRORED",
            CustomEnchantmentIds::MIRRORED,
            new MirroredEnchantment(
                "Mirrored", CERarity::MASTERY, "Chance reflect damage that would be onto you\nback to your attacker",
                3, ItemFlags::TORSO, ItemFlags::NONE
            )
        );

        self::register(
            "TRAPPED",
            CustomEnchantmentIds::TRAPPED,
            new TrappedEnchantment(
                "Trapped", CERarity::MASTERY, "Chance to trap your opponent in cobwebs\nup to 5 seconds",
                5, ItemFlags::HEAD, ItemFlags::NONE
            )
        );

        self::register(
            "ASCENDED",
            CustomEnchantmentIds::ASCENDED,
            new AscendedEnchantment(
                "Ascended", CERarity::MASTERY, "Chance to shoot you up in the air when low hp",
                5, ItemFlags::FEET, ItemFlags::NONE
            )
        );

        self::register(
            "REDEEMER",
            CustomEnchantmentIds::REDEEMER,
            new RedeemerEnchantment(
                "Redeemer", CERarity::MASTERY, "Chance to buff your damage up to 3x for up to 3 seconds",
                3, ItemFlags::LEGS, ItemFlags::NONE
            )
        );

        self::register(
            "SATANSTREAT",
            CustomEnchantmentIds::SATANSTREAT,
            new SatansTreatEnchantment(
                "Satan's Treat", CERarity::MASTERY, "Chance to drop crate keys",
                5, ItemFlags::PICKAXE, ItemFlags::NONE
            )
        );
    }

    protected static function register(string $name, int $id, CustomEnchantment $enchantment) : void {
        $map = EnchantmentIdMap::getInstance();
        $map->register($id, $enchantment);
        StringToEnchantmentParser::getInstance()->register($enchantment->getName(), fn() => $enchantment); //todo: needed?

        self::$ids[$enchantment->getName()] = $id;
        self::$rarities[$enchantment->getRarity()][] = $id;
        self::_registryRegister($name, $enchantment);
    }

    public static function getIdFromName(string $name) : ?int {
        return self::$ids[$name] ?? null;
    }

    public static function getAll() : array{
        /**
         * @var CustomEnchantment[] $result
         * @phpstan-var array<string, CustomEnchantment> $result
         */
        $result = self::_registryGetAll();
        return $result;
    }

    public static function getAllForRarity(RarityType $type) : array {
        return self::$rarities[$type->getId()];
    }

    public static function display(ItemStack $itemStack) : ItemStack {
        $item = TypeConverter::getInstance()->netItemStackToCore($itemStack);

        if (count($item->getEnchantments()) > 0) {
            $additionalInformation = TextFormat::RESET . TextFormat::AQUA . $item->getName();
            foreach ($item->getEnchantments() as $enchantmentInstance) {
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchantment) {
                    $additionalInformation .= "\n" . TextFormat::RESET . RarityType::fromId($enchantment->getRarity())->getColor() . $enchantment->getName() . " " . Utils::getRomanNumeral($enchantmentInstance->getLevel());
                }
            }
            if ($item->getNamedTag()->getTag(Item::TAG_DISPLAY)) $item->getNamedTag()->setTag("OriginalDisplayTag", $item->getNamedTag()->getTag(Item::TAG_DISPLAY)->safeClone());
            $item = $item->setCustomName($additionalInformation);
        }
        return TypeConverter::getInstance()->coreItemStackToNet($item);
    }

    public static function filter(ItemStack $itemStack): ItemStack {
        $item = TypeConverter::getInstance()->netItemStackToCore($itemStack);
        $tag = $item->getNamedTag();
        if (count($item->getEnchantments()) > 0) $tag->removeTag(Item::TAG_DISPLAY);

        if ($tag->getTag("OriginalDisplayTag") instanceof CompoundTag) {
            $tag->setTag(Item::TAG_DISPLAY, $tag->getTag("OriginalDisplayTag"));
            $tag->removeTag("OriginalDisplayTag");
        }
        $item->setNamedTag($tag);
        return TypeConverter::getInstance()->coreItemStackToNet($item);
    }


    /**
     * @param EnchantmentInstance[] $enchantments
     * @return EnchantmentInstance[]
     */
    public static function sortEnchantmentsByRarity(array $enchantments): array
    {
        usort($enchantments, function (EnchantmentInstance $enchantmentInstance, EnchantmentInstance $enchantmentInstanceB) {
            $type = $enchantmentInstance->getType();
            $typeB = $enchantmentInstanceB->getType();
            return ($typeB instanceof CustomEnchantment ? $typeB->getRarity() : 1) - ($type instanceof CustomEnchantment ? $type->getRarity() : 1);
        });
        return $enchantments;
    }
}