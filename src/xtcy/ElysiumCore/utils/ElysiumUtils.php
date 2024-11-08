<?php

namespace xtcy\ElysiumCore\utils;

use DaPigGuy\PiggyFactions\libs\Vecnavium\FormsUI\CustomForm;
use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\Entity;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\PotionType;
use pocketmine\item\StringToItemParser;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\LuckyPouches\utils\PouchItem;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\enchants\util\CustomEnchantmentIds;
use xtcy\ElysiumCore\items\Items;
use xtcy\ElysiumCore\Loader;

use function PHPSTORM_META\map;

class ElysiumUtils
{

    public static array $tpaRequests = [];
    
    public static array $tpahereRequests = [];

    public static function sendUpdate(Player $player): void{
        (new PlayerTagsUpdateEvent($player, [
            new ScoreTag("elysium.balance", number_format(Loader::getPlayerManager()->getSession($player)->getBalance())),
            new ScoreTag("elysium.kills", number_format(Loader::getPlayerManager()->getSession($player)->getKills())),
            new ScoreTag("elysium.deaths", number_format(Loader::getPlayerManager()->getSession($player)->getDeaths())),
            new ScoreTag("elysium.gems", number_format(Loader::getPlayerManager()->getSession($player)->getGems())),
            new ScoreTag("elysium.level", number_format(Loader::getPlayerManager()->getSession($player)->getLevel())),
            new ScoreTag("elysium.xp", number_format($player->getXpManager()->getCurrentTotalXp())),
            //new ScoreTag("elysium.kdr", Loader::getPlayerManager()->getSession($player)->getKDRRatio())
        ]))->call();
    }

    public static function toggleFlight(Player $player, bool $force = false): void
    {
        if ($force) {
            if (!$player->getAllowFlight()) {
                $player->setAllowFlight(true);
                $player->sendMessage(C::colorize("&r&l&a! &r&aYou have enabled flight."));
            }
        } else {
            if (!$player->getAllowFlight()) {
                $player->setAllowFlight(true);
                $player->sendMessage(C::colorize("&r&l&a! &r&aYou have enabled flight."));
            } else {
                $player->setAllowFlight(false);
                $player->setFlying(false);
                $player->resetFallDistance();
                $player->sendMessage(C::colorize("&r&l&c! &r&cYou have disabled flight."));
            }
        }
    
        if ($force || $player->getAllowFlight() && !$force) {
            $player->setFlying(true);
            $player->resetFallDistance();
        }
    }
    
    public static function spawnParticle(Entity $player, string $particleName, float $x, float $y, float $z, int $radius = 5): void {
        $packet = new SpawnParticleEffectPacket();
        $packet->particleName = $particleName;
        $packet->position = new Vector3($x, $y, $z); // Create Vector3 from integer coordinates

        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
            if ($p instanceof Player) {
                if ($p->isOnline()) {
                    $p->getNetworkSession()->sendDataPacket($packet);
                }
            }
        }
    }

    public static function applyDisplayEnchant(Item $item): void {
        $item->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(CustomEnchantmentIds::FAKE_ENCH_ID)));
    }
    
    public static function sendPurchaseConfirmation(Player $player, Item $item, int $price): CustomForm {
        $itemName = $item->getName();
        $session = Loader::getPlayerManager()->getSession($player);
        $form = new CustomForm(function (Player $player, $data) use ($item, $price, $session) {
            if ($data === null) {
                return true;
            }
    
            $amount = (int) $data["Amount"];
            if (!is_numeric($amount) || $amount <= 0) {
                $player->sendMessage(C::colorize("&r&cPlease enter a valid amount."));
                return;
            }
    
            $totalPrice = $price * $amount;
            if ($session->getBalance() >= $totalPrice) {
                if ($player->getInventory()->canAddItem($item->setCount($amount))) {
                    $session->subtractBalance($totalPrice);
                    $player->getInventory()->addItem($item->setCount($amount));
                    $player->sendMessage(C::colorize("&r&aSuccessfully purchased {$amount}x {$item->getName()} for $" . number_format($totalPrice) . "!"));
                } else {
                    $player->sendPopup(C::colorize("&r&cYou don't have enough inventory space to buy {$amount}x {$item->getName()}."));
                }
            } else {
                $player->sendToastNotification(C::colorize("&r&c&l(!) &r&cInsufficient Funds!"), C::colorize("&r&7You need at least $" . number_format($totalPrice) . " to buy this item. You have: $" . number_format($session->getBalance())));
            }
        });
    
        $form->setTitle(C::colorize("&r&8Shop Confirmation"));
        $form->addInput(C::colorize("&r&aEnter the amount of $itemName you want to buy:"), "Max: 2304", 1, "Amount");
    
        return $form;
    }    

    public static function sendQuestPurchaseConfirmation(Player $player, Item $item, int $price): CustomForm {
        $itemName = $item->getName();
        $session = Loader::getPlayerManager()->getSession($player);
        $form = new CustomForm(function (Player $player, $data) use ($item, $price, $session) {
            if ($data === null) {
                return true;
            }
    
            $amount = (int) $data["Amount"];
            if (!is_numeric($amount) || $amount <= 0) {
                $player->sendMessage(C::colorize("&r&cPlease enter a valid amount."));
                return;
            }
    
            $totalPrice = $price * $amount;
            if ($session->getQuestTokens() >= $totalPrice) {
                if ($player->getInventory()->canAddItem($item->setCount($amount))) {
                    $session->subtractQuestTokens($totalPrice);
                    $player->getInventory()->addItem($item->setCount($amount));
                    $player->sendMessage(C::colorize("&r&aSuccessfully purchased {$amount}x {$item->getName()} for " . number_format($totalPrice) . " Quest Tokens!"));
                } else {
                    $player->sendPopup(C::colorize("&r&cYou don't have enough inventory space to buy {$amount}x {$item->getName()}."));
                }
            } else {
                $player->sendToastNotification(C::colorize("&r&c&l(!) &r&cInsufficient Funds!"), C::colorize("&r&7You need at least " . number_format($totalPrice) . " quest tokens to buy this item. You have: " . number_format($session->getQuestTokens())));
            }
        });
    
        $form->setTitle(C::colorize("&r&8Shop Confirmation"));
        $form->addInput(C::colorize("&r&aEnter the amount of $itemName you want to buy:"), "Max: 2304", 1, "Amount");
    
        return $form;
    }  
    
    public static function sendEnchanterPurchaseConfirmation(Player $player, Item $item, int $price): CustomForm {
        $itemName = $item->getName();
        $session = Loader::getPlayerManager()->getSession($player);
        $form = new CustomForm(function (Player $player, $data) use ($item, $price, $session) {
            if ($data === null) {
                return true;
            }
    
            $amount = (int) $data["Amount"];
            if (!is_numeric($amount) || $amount <= 0) {
                $player->sendMessage(C::colorize("&r&cPlease enter a valid amount."));
                return;
            }
    
            $totalPrice = $price * $amount;
            if ($player->getXpManager()->getCurrentTotalXp() >= $totalPrice) {
                if ($player->getInventory()->canAddItem($item->setCount($amount))) {
                    $session->subtractEXP($totalPrice);
                    $player->getInventory()->addItem($item->setCount($amount));
                    $player->sendMessage(C::colorize("&r&aSuccessfully purchased {$amount}x {$item->getName()} &r&afor " . number_format($totalPrice) . " EXP!"));
                } else {
                    $player->sendPopup(C::colorize("&r&cYou don't have enough inventory space to buy {$amount}x {$item->getName()}."));
                }
            } else {
                $player->sendToastNotification(C::colorize("&r&c&l(!) &r&cInsufficient Funds!"), C::colorize("&r&7You need at least " . number_format($totalPrice) . " EXP to buy this item. You have: " . number_format($player->getXpManager()->getCurrentTotalXp())));
            }
        });
    
        $form->setTitle(C::colorize("&r&8Shop Confirmation"));
        $form->addInput(C::colorize("&r&aEnter the amount of $itemName you want to buy:"), "Max: 2304", 1, "Amount");
    
        return $form;
    }    
    
    public static function checkRequestTimeout(string $targetName, string $type): void {
        if (isset(self::${$type}[$targetName]) && (time() - self::${$type}[$targetName]['time']) >= 60) {
            $requesterName = self::${$type}[$targetName]['requester'];
            $requester = Utils::getPlayerByPrefix($requesterName);
            if ($requester !== null && $requester->isOnline()) {
                $requester->sendMessage(C::colorize("&r&l&c(!) &r&cYour teleport request to " . $targetName . " has expired."));
            }
            unset(self::${$type}[$targetName]);
        }
    }

    public static function openLootbox(Player $player, string $lootboxName): void
    {
        $lootTables = [
            "end_of_summer" => [
                "randomLoot" => [
                    "Golden Apple",
                    "Diamond Sword (Sharpness V)",
                    // Add other random loot items here
                ],
                "jackpotLoot" => [
                    "Elytra",
                    "Beacon",
                    // Add other jackpot loot items here
                ],
                "bonusLoot" => [
                    "Exclusive Title: Summer Hero",
                    "Custom Particle Effect (Fireworks)",
                    // Add other bonus loot items here
                ]
            ],
            "stormcaller" => [
                "randomLoot" => [
                    Items::createBossEgg("broodmother", 1),
                    PouchItem::getPouchType("ultimate_xp"),
                    Items::getCrateKey("zenith", 3),
                    Items::getCrateKey("cipher", 4),
                    Items::getEnchantScrolls("whitescroll"),
                    Items::getEnchantScrolls("transmog"),
                    Items::getEnchantScrolls("blackscroll", 1, mt_rand(1, 100)),
                    Items::getEnchantScrolls("lorecrystal"),
                    Items::createRandomCEBook("legendary"),
                    Items::createEnchantFragment("fire_aspect", 1),
                    Items::createPerkVoucher("generator", 1),
                ],
                "jackpotLoot" => [

                ],
                "bonusLoot" => [
                    Items::createTitleVoucher("Stormcaller", 1),
                ]
            ]
        ];

        if (!isset($lootTables[$lootboxName])) {
            $player->sendMessage("Invalid lootbox name: $lootboxName");
            return;
        }

        $randomLoot = $lootTables[$lootboxName]["randomLoot"];
        $jackpotLoot = $lootTables[$lootboxName]["jackpotLoot"];
        $bonusLoot = $lootTables[$lootboxName]["bonusLoot"];

        $randomItems = self::getRandomItems($randomLoot, 4);

        $player->getInventory()->addItem(...$randomItems);


        if (!empty($jackpotLoot)) {
            $jackpotItem = self::getRandomItem($jackpotLoot);
            $player->getInventory()->addItem($jackpotItem);
        }

        if (!empty($bonusLoot)) {
            $bonusItem = self::getRandomItem($bonusLoot);
            $player->getInventory()->addItem($bonusItem);
        }

        foreach (Server::getInstance()->getOnlinePlayers() as $oplayer) {
            $session = Loader::getPlayerManager()->getSession($oplayer);
            if ($session->getSetting("lootbox_broadcast") === true) {
                $openingPlayerSession = Loader::getPlayerManager()->getSession($player);
                if ($openingPlayerSession->getSetting("lootbox_broadcast") === true) {
                    $broadcastMessage = C::colorize("&r&l&e(!) &r&e" . $player->getName() . " has just opened the &f&l" . ucfirst(str_replace('_', ' ', $lootboxName)) . " &r&eLootbox and has gotten:");
                    
                    $itemsToBroadcast = array_merge($randomItems, !empty($jackpotLoot) ? [$jackpotItem] : [], !empty($bonusLoot) ? [$bonusItem] : []);
                    foreach ($itemsToBroadcast as $item) {
                        $itemName = $item->getName();
                        $itemCount = $item->getCount();
                        if ($itemName !== "Air") {
                            $broadcastMessage .= C::colorize("\n&r&6&l* &e" . $itemCount . "x " . $itemName);
                        }
                    }
        
                    $oplayer->sendMessage($broadcastMessage);
                }
            }
        }        
    }

    public static function getRandomItems(array $items, int $count): array
    {
        $shuffled = $items;
        shuffle($shuffled);
        return array_slice($shuffled, 0, $count);
    }

    public static function getRandomItem(array $items)
    {
        return $items[array_rand($items)];
    }

    public static function isSellable(Item $item): bool {
        if ($item instanceof Armor || $item instanceof Tool || $item instanceof Sword) {
            return false;
        }

        if ($item->hasCustomName() || $item->hasNamedTag()) {
            return false;
        }

        return true;
    }

    public static function getSymbol(string $role): string {
        switch ($role) {
            case "leader":
                return "***"; 
            case "officer":
                return "**"; 
            case "member":
                return ""; 
            case "recruit":
                return "-"; 
            default:
                return ""; 
        }
    } 

    public static function getSellPrice(Item $item): int {

        $prices = [
            VanillaItems::DIAMOND()->getTypeId() => 150,
            VanillaItems::EMERALD()->getTypeId() => 100,
            VanillaItems::IRON_INGOT()->getTypeId() => 30,
            VanillaItems::GOLD_INGOT()->getTypeId() => 40,
            VanillaItems::LAPIS_LAZULI()->getTypeId() => 1,
            VanillaItems::COAL()->getTypeId() => 1,
            VanillaItems::REDSTONE_DUST()->getTypeId() => 1,
            VanillaBlocks::IRON()->asItem()->getTypeId() => 1080,
            VanillaBlocks::COAL()->asItem()->getTypeId() => 8,
            VanillaBlocks::GOLD()->asItem()->getTypeId() => 450,
            VanillaBlocks::DIAMOND()->asItem()->getTypeId() => 1800,
            VanillaBlocks::EMERALD()->asItem()->getTypeId() => 2700,
            VanillaBlocks::LAPIS_LAZULI()->asItem()->getTypeId() => 9,
            VanillaBlocks::REDSTONE()->asItem()->getTypeId() => 10,
            StringToItemParser::getInstance()->parse("piston")->getTypeId() => 25,
            VanillaBlocks::STONE()->asItem()->getTypeId() => 1,
            VanillaBlocks::GLOWSTONE()->asItem()->getTypeId() => 2,
            VanillaBlocks::SPONGE()->asItem()->getTypeId() => 100,
            VanillaBlocks::TNT()->asItem()->getTypeId() => 8,
            VanillaBlocks::GLASS()->asItem()->getTypeId() => 1,
            VanillaBlocks::STONE_SLAB()->asItem()->getTypeId() => 1,
            VanillaBlocks::REDSTONE_TORCH()->asItem()->getTypeId() => 5,
            VanillaBlocks::REDSTONE_COMPARATOR()->asItem()->getTypeId() => 25,
            VanillaBlocks::REDSTONE_REPEATER()->asItem()->getTypeId() => 10,
            StringToItemParser::getInstance()->parse("creeper_spawn_egg")->getTypeId() => 10000,
            VanillaBlocks::REDSTONE()->asItem()->getTypeId() => 10,
            VanillaBlocks::OAK_TRAPDOOR()->asItem()->getTypeId() => 1,
            VanillaBlocks::SLIME()->asItem()->getTypeId() => 1,
            VanillaBlocks::OAK_WOOD()->asItem()->getTypeId() => 1,
            VanillaBlocks::BIRCH_WOOD()->asItem()->getTypeId() => 1,
            VanillaBlocks::JUNGLE_WOOD()->asItem()->getTypeId() => 1,
            VanillaBlocks::SPRUCE_WOOD()->asItem()->getTypeId() => 1,
            VanillaBlocks::PODZOL()->asItem()->getTypeId() => 1,
            VanillaBlocks::MYCELIUM()->asItem()->getTypeId() => 1,
            VanillaBlocks::STONE_BRICKS()->asItem()->getTypeId() => 1,
            VanillaBlocks::MOSSY_STONE_BRICKS()->asItem()->getTypeId() => 1,
            VanillaBlocks::CRACKED_STONE_BRICKS()->asItem()->getTypeId() => 1,
            VanillaBlocks::CHISELED_STONE_BRICKS()->asItem()->getTypeId() => 1,
            VanillaBlocks::SANDSTONE()->asItem()->getTypeId() => 1,
            VanillaBlocks::CHISELED_SANDSTONE()->asItem()->getTypeId() => 1,
            VanillaBlocks::SMOOTH_SANDSTONE()->asItem()->getTypeId() => 1,
            VanillaBlocks::QUARTZ()->asItem()->getTypeId() => 1,
            VanillaBlocks::CHISELED_QUARTZ()->asItem()->getTypeId() => 1,
            VanillaBlocks::QUARTZ_PILLAR()->asItem()->getTypeId() => 1,
            VanillaBlocks::NETHER_BRICKS()->asItem()->getTypeId() => 1,
            VanillaBlocks::SOUL_SAND()->asItem()->getTypeId() => 1,
            VanillaBlocks::END_STONE()->asItem()->getTypeId() => 1,
            VanillaBlocks::OBSIDIAN()->asItem()->getTypeId() => 8,
            VanillaBlocks::DARK_OAK_WOOD()->asItem()->getTypeId() => 1,
            VanillaBlocks::DARK_OAK_SLAB()->asItem()->getTypeId() => 1,
            VanillaBlocks::CACTUS()->asItem()->getTypeId() => 11,
            VanillaBlocks::SUGARCANE()->asItem()->getTypeId() => 9,
            VanillaItems::MELON()->getTypeId() => 3,
            VanillaBlocks::PUMPKIN()->getTypeId() => 2,        
            VanillaItems::WHEAT()->getTypeId() => 2,
            VanillaItems::APPLE()->getTypeId() => 10,
            VanillaItems::GOLDEN_APPLE()->getTypeId() => 5,
            VanillaBlocks::CAKE()->getTypeId() => 5,
            VanillaItems::BREAD()->getTypeId() => 6,
            VanillaItems::STEAK()->getTypeId() => 1,
            VanillaItems::COOKED_PORKCHOP()->getTypeId() => 1,
            VanillaItems::CARROT()->getTypeId() => 9,
            VanillaItems::POTATO()->getTypeId() => 9,
            VanillaItems::GUNPOWDER()->getTypeId() => 1,
            VanillaItems::ARROW()->getTypeId() => 1,
            VanillaItems::BLAZE_ROD()->getTypeId() => 4,
            VanillaBlocks::LILY_PAD()->asItem()->getTypeId() => 10,
            VanillaItems::FEATHER()->getTypeId() => 1,
            VanillaItems::ROTTEN_FLESH()->getTypeId() => 1,
            VanillaItems::STRING()->getTypeId() => 1,
            VanillaItems::SPIDER_EYE()->getTypeId() => 1,
            VanillaItems::LEATHER()->getTypeId() => 2,
            VanillaItems::ENDER_PEARL()->getTypeId() => 2,
            VanillaItems::BONE()->getTypeId() => 1,
            VanillaItems::GHAST_TEAR()->getTypeId() => 30, 
            VanillaItems::POTION()->setType(PotionType::WATER())->getTypeId() => 1,
            VanillaItems::GOLDEN_CARROT()->getTypeId() => 25,
            VanillaItems::SLIMEBALL()->getTypeId() => 12,
            VanillaBlocks::NETHER_WART()->asItem()->getTypeId() => 3,
            VanillaItems::GLISTERING_MELON()->getTypeId() => 9,
            VanillaBlocks::WOOL()->getTypeId() => 1,
            VanillaBlocks::CARPET()->getTypeId() => 1,
            VanillaBlocks::GLASS()->asItem()->getTypeId() => 1,
            VanillaBlocks::STAINED_GLASS()->asItem()->getTypeId() => 1,
            VanillaBlocks::STAINED_GLASS_PANE()->asItem()->getTypeId() => 1,
            VanillaBlocks::GLASS_PANE()->asItem()->getTypeId() => 1,
            VanillaBlocks::CLAY()->asItem()->getTypeId() => 1,
            VanillaBlocks::STAINED_CLAY()->asItem()->getTypeId() => 1,
            VanillaBlocks::POPPY()->asItem()->getTypeId() => 4,
            VanillaBlocks::BLUE_ORCHID()->asItem()->getTypeId() => 4,
            VanillaBlocks::ALLIUM()->asItem()->getTypeId() => 4,
            VanillaBlocks::AZURE_BLUET()->asItem()->getTypeId() => 4,
            VanillaBlocks::RED_TULIP()->asItem()->getTypeId() => 4,
            VanillaBlocks::ORANGE_TULIP()->asItem()->getTypeId() => 4,
            VanillaBlocks::WHITE_TULIP()->asItem()->getTypeId() => 4,
            VanillaBlocks::PINK_TULIP()->asItem()->getTypeId() => 4,
            VanillaBlocks::OXEYE_DAISY()->asItem()->getTypeId() => 4,
            VanillaBlocks::FLOWER_POT()->asItem()->getTypeId() => 30,
        ];

        foreach ($prices as $key => $value) {
            if ($item->getTypeId() === $key) {
                return $value;
            }
         }

        return 0; 
    }

    public static function getRewardForPlace(int $place, string $incursionType): Item {
        $item = VanillaItems::AIR();
        switch ($place) {
            case 1:
                switch ($incursionType) {
                    case "astral":
                        $item = Items::getCrateKey("empyrean", 1);
                    case "soul":
                        $item = Items::getCrateKey("empyrean", 2);
                    case "hollow":
                        $item = Items::getCrateKey("empyrean", 3);
                }

                break;
            case 2:
                switch ($incursionType) {
                    case "astral":
                        $item = Items::getCrateKey("zenith", 1);
                    case "soul":
                        $item = Items::getCrateKey("zenith", 2);
                    case "hollow":
                        $item = Items::getCrateKey("zenith", 3);
                }
                break;
            case 3:
                switch ($incursionType) {
                    case "astral":
                        $item = Items::getCrateKey("cipher", 1);
                    case "soul":
                        $item = Items::getCrateKey("cipher", 2);
                    case "hollow":
                        $item = Items::getCrateKey("cipher", 3);
                }
                break;


        }
    
        return $item;
    }
    
    public static function updateEntityNameTag(Entity $entity): void {
        $totalBars = 20; 
        $currentHealth = $entity->getHealth();
        $maxHealth = $entity->getMaxHealth();
        $currentHealthBars = (int) round(($currentHealth / $maxHealth) * $totalBars);
    
        $healthBar = "";
        for ($i = 0; $i < $totalBars; $i++) {
            $healthColor = self::getHealthColor(ceil(($currentHealth / $maxHealth) * 4));
            $healthBar .= $i < $currentHealthBars ? $healthColor . "|" : "§c|";
        }
    
        $entity->setNameTag($entity->getNameTag() . "\n" . $healthBar);
    }
    
    public static function getHealthColor(int $healthMultiplier): string {
        switch ($healthMultiplier) {
            case 1:
                return "§a"; // Green for health <= 20
            case 2:
                return "§e"; // Yellow for health > 20 and <= 40
            case 3:
                return "§c"; // Red for health > 40 and <= 60
            default:
                return "§6"; // Gold for health > 60
        }
    }
}