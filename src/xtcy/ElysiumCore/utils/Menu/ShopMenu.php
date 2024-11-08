<?php

namespace xtcy\ElysiumCore\utils\Menu;

use DaPigGuy\PiggyFactions\libs\Vecnavium\FormsUI\CustomForm;
use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\PotionType;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

use Vecnavium\FormsUI\SimpleForm;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class ShopMenu {

    public static function getShopCategoriesForm(Player $player): SimpleForm {
        $form = new SimpleForm(function(Player $player, $data): void {
            if ($data === null) return;

            switch ($data) {
                case 0:
                     $player->sendForm(self::getShopCategoryForm("potion", $player));
                    break;
                case 1:
                    $player->sendForm(self::getShopCategoryForm("raid", $player));
                    break;
                case 2:
                    $player->sendForm(self::getShopCategoryForm("spawners", $player));    
                    break;
                case 3:
                    $player->sendForm(self::getShopCategoryForm("building_blocks", $player));
                    break;
                case 4:
                    $player->sendForm(self::getShopCategoryForm("ores_and_gems", $player));
                    break;
                case 5:
                    $player->sendForm(self::getShopCategoryForm("food_and_farming", $player));
                    break;
                case 6:
                    $player->sendForm(self::getShopCategoryForm("mob_drops", $player));
                    break;
                case 7:
                    $player->sendForm(self::getShopCategoryForm("speciality", $player));
                    break;
                case 8:
                    $player->sendForm(self::getShopCategoryForm("brewing", $player));
                    break;
                case 9:
                    $player->sendForm(self::getShopCategoryForm("wool", $player));
                    break;
                case 10:
                    $player->sendForm(self::getShopCategoryForm("glass", $player));
                    break;
                case 11:
                    $player->sendForm(self::getShopCategoryForm("base", $player));
                    break;
                case 12:
                    $player->sendForm(self::getShopCategoryForm("clay", $player));
                    break;
                case 13:
                    $player->sendForm(self::getShopCategoryForm("flowers", $player));
                    break;
                }
            });

        $form->setTitle(C::colorize("&r&8Shop Categories"));
        $form->addButton(C::colorize("&r&l&ePotion Shop\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eRaid Shop\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eSpawners Shop\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eBuilding Blocks Shop\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eOres and Gems Shop\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eFood and Farming\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eMob Drops\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eSpeciality\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eBrewing\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eWool\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eGlass\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eBase Grind\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eClay\n&r&8Click to view this category"));
        $form->addButton(C::colorize("&r&l&eFlowers\n&r&8Click to view this category"));

        return $form;
    }

    public static function getShopCategoryForm(string $shop, Player $player): SimpleForm {
        $form = new SimpleForm(function(Player $player, $data): void {
            if ($data === null) return;
        });
        $session = Loader::getPlayerManager()->getSession($player);

        switch ($shop) {
            case "potion":
                $form = new SimpleForm(function(Player $player, $data) use ($session): void {
                    if ($data === null) return;
                    
                    if ($session !== null) {
                        switch ($data) {
                            case 0:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::POTION()->setType(PotionType::STRONG_REGENERATION()), 120));
                                break;
                            case 1:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_REGENERATION()), 145));
                                break;   
                            case 2:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::POTION()->setType(PotionType::STRONG_SWIFTNESS()), 30));
                                break;   
                            case 3:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_SWIFTNESS()), 55));
                                break;
                            case 4:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::POTION()->setType(PotionType::WATER_BREATHING()), 225));
                                break;
                            case 5:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_STRENGTH()), 110)); 
                                break;
                            case 6:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::POTION()->setType(PotionType::STRONG_STRENGTH()), 85));
                                break;
                            case 7:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALTH()), 75));
                                break;
                            case 8:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::SPLASH_POTION()->setType(PotionType::FIRE_RESISTANCE()), 1000));
                                break;
                            case 9:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::POTION()->setType(PotionType::STRONG_HEALTH())->setCount(2), 250));
                                break;
                            case 10:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::POTION()->setType(PotionType::STRONG_HEALTH())->setCount(4), 500));
                                break;
                            case 11:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::POTION()->setType(PotionType::STRONG_HEALTH())->setCount(8), 1000));
                            break;
                        }
                    }
                });
                $form->setTitle(C::colorize("&r&8Shop Items (Potion Shop)"));
                $form->addButton(C::colorize("&r&l&eRegeneration Potion II\n&r&aBuy Price: $120/ea"));
                $form->addButton(C::colorize("&r&l&eRegeneration Splash II\n&r&aBuy Price: $145/ea"));
                $form->addButton(C::colorize("&r&l&eSwiftness Potion II\n&r&aBuy Price: $30/ea"));
                $form->addButton(C::colorize("&r&l&eSwiftness Splash II\n&r&aBuy Price: $55/ea"));
                $form->addButton(C::colorize("&r&l&eWater Breathing Potion\n&r&aBuy Price: $225/ea"));
                $form->addButton(C::colorize("&r&l&eStrength Potion II\n&r&aBuy Price: $110/ea"));
                $form->addButton(C::colorize("&r&l&eStrength Splash II\n&r&aBuy Price: $85/ea"));
                $form->addButton(C::colorize("&r&l&eInstant Health II\n&r&aBuy Price: $75/ea"));
                $form->addButton(C::colorize("&r&l&eFire Resistance Potion\n&r&aBuy Price: $1,000/ea"));
                $form->addButton(C::colorize("&r&l&e2x Instant Health II\n&r&aBuy Price: $250/ea"));
                $form->addButton(C::colorize("&r&l&e4x Instant Health II\n&r&aBuy Price: $500/ea"));
                $form->addButton(C::colorize("&r&l&e8x Instant Health II\n&r&aBuy Price: $1,000/ea"));
                break;
            case "raid":
                $items = [
                    ["Redstone", VanillaItems::REDSTONE_DUST(), 16],
                    ["Redstone Block", VanillaBlocks::REDSTONE()->asItem(), 195],
                    ["Redstone Torch", VanillaBlocks::REDSTONE_TORCH()->asItem(), 25],
                    ["Redstone Comparator", VanillaBlocks::REDSTONE_COMPARATOR()->asItem(), 100],
                    ["Redstone Repeater", VanillaBlocks::REDSTONE_REPEATER()->asItem(), 85],
                    ["Stone Button", VanillaBlocks::STONE_BUTTON()->asItem(), 5],
                    ["Wood Button", VanillaBlocks::OAK_BUTTON()->asItem(), 5],
                    ["TNT", VanillaBlocks::TNT()->asItem(), 81],
                    ["Dispenser", StringToItemParser::getInstance()->parse("dispenser"), 895],
                    ["Sticky Piston", StringToItemParser::getInstance()->parse("sticky_piston"), 325],
                    ["Piston", StringToItemParser::getInstance()->parse("piston"), 195],
                    ["Glowstone", VanillaBlocks::GLOWSTONE()->asItem(), 5],
                    ["Glass", VanillaBlocks::GLASS()->asItem(), 4],
                    ["Ladder", VanillaBlocks::LADDER()->asItem(), 10],
                    ["Web", VanillaBlocks::COBWEB()->asItem(), 40],
                    ["Sponge", VanillaBlocks::SPONGE()->asItem(), 2000],
                    ["Lever", VanillaBlocks::LEVER()->asItem(), 20],
                    ["Wooden Trapdoor", VanillaBlocks::OAK_TRAPDOOR()->asItem(), 50],
                    ["Stone", VanillaBlocks::STONE()->asItem(), 5],
                    ["Oak Wood", VanillaBlocks::OAK_WOOD()->asItem(), 5],
                    ["Sand", VanillaBlocks::SAND()->asItem(), 4],
                    ["Red Sand", VanillaBlocks::RED_SAND()->asItem(), 4],
                    ["Gravel", VanillaBlocks::GRAVEL()->asItem(), 3],
                    ["Ice", VanillaBlocks::ICE()->asItem(), 50],
                    ["Water Bucket", VanillaItems::WATER_BUCKET(), 500],
                    ["Lava Bucket", VanillaItems::LAVA_BUCKET(), 500],
                    ["Stone Slab", VanillaBlocks::STONE_SLAB()->asItem(), 50],
                    ["Spawn Creeper", StringToItemParser::getInstance()->parse("creeper_spawn_egg"), 250000],
                    ["Flint and Steel", VanillaItems::FLINT_AND_STEEL(), 500],
                    ["Fishing Rod", VanillaItems::FISHING_ROD(), 500],
                ];
                
                $form = new SimpleForm(function (Player $player, $data) use ($session, $items): void {
                    if ($data === null || $session === null) return;
                
                    $selectedItem = $items[$data];
                    $itemName = $selectedItem[0];
                    $item = $selectedItem[1];
                    $price = $selectedItem[2];
                
                    $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, $item, $price));
                });
                
                $form->setTitle(C::colorize("&r&8Shop Items (Raid Shop)"));
                
                foreach ($items as $item) {
                    $itemName = $item[0];
                    $price = $item[2];
                    $form->addButton(C::colorize("&r&l&e{$itemName}\n&r&aBuy Price: \${$price}/ea"));
                }
                break;
            case "spawners":
                $form = new SimpleForm(function(Player $player, $data) use ($session): void {
                    if ($data === null) return;

                    if ($session !== null) {
                        switch ($data) {
                            case 0:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("iron_golem_spawner"), 5000000));
                                break;
                            case 1:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("blaze_spawner"), 750000));
                                break;
                            case 2:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("creeper_spawner"), 390000));
                                break;
                            case 3:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("enderman_spawner"), 390000));
                                break;
                            case 4:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("zombie_pigman_spawner"), 450000));
                                break;
                            case 5:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("cave_spider_spawner"), 90000));
                                break;
                            case 6:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("spider_spawner"), 95000));
                                break;
                            case 7:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("skeleton_spawner"), 115000));   
                                break;
                            case 8:
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("zombie_spawner"), 115000));
                                break; 
                            }
                    }
                });
                $form->setTitle(C::colorize("&r&8Shop Items (Spawners)"));
                $form->addButton(C::colorize("&r&l&dIron Golem &fSpawner\n&r&aBuy Price: $5,000,000/ea"));
                $form->addButton(C::colorize("&r&l&dBlaze &fSpawner\n&r&aBuy Price: $750,000/ea"));
                $form->addButton(C::colorize("&r&l&dCreeper &fSpawner\n&r&aBuy Price: $390,000/ea"));
                $form->addButton(C::colorize("&r&l&dEnderman &fSpawner\n&r&aBuy Price: $390,000/ea"));
                $form->addButton(C::colorize("&r&l&dZombie Pigman &fSpawner\n&r&aBuy Price: $450,000/ea"));
                $form->addButton(C::colorize("&r&l&dCave Spider &fSpawner\n&r&aBuy Price: $90,000/ea"));
                $form->addButton(C::colorize("&r&l&dSpider &fSpawner\n&r&aBuy Price: $95,000/ea"));
                $form->addButton(C::colorize("&r&l&dSkeleton &fSpawner\n&r&aBuy Price: $115,000/ea"));
                $form->addButton(C::colorize("&r&l&dZombie &fSpawner\n&r&aBuy Price: $115,000/ea"));

                break;
            case "building_blocks":
                $items = [
                    ['block' => VanillaBlocks::GRASS(), 'price' => 3, 'label' => 'Grass Block'],
                    ['block' => VanillaBlocks::OAK_WOOD(), 'price' => 8, 'label' => 'Oak Wood'],
                    ['block' => VanillaBlocks::BIRCH_WOOD(), 'price' => 8, 'label' => 'Birch Wood'],
                    ['block' => VanillaBlocks::JUNGLE_WOOD(), 'price' => 8, 'label' => 'Jungle Wood'],
                    ['block' => VanillaBlocks::DIRT(), 'price' => 3, 'label' => 'Dirt'],
                    ['block' => VanillaBlocks::PODZOL(), 'price' => 8, 'label' => 'Podzol'],
                    ['block' => VanillaBlocks::MYCELIUM(), 'price' => 8, 'label' => 'Mycelium'],
                    ['block' => VanillaBlocks::STONE(), 'price' => 40, 'label' => 'Stone'],
                    ['block' => VanillaBlocks::COBBLESTONE(), 'price' => 30, 'label' => 'Cobblestone'],
                    ['block' => VanillaBlocks::STONE_BRICKS(), 'price' => 60, 'label' => 'Stone Bricks'],
                    ['block' => VanillaBlocks::MOSSY_STONE_BRICKS(), 'price' => 60, 'label' => 'Mossy Stone Brick'],
                    ['block' => VanillaBlocks::CRACKED_STONE_BRICKS(), 'price' => 60, 'label' => 'Cracked Stone Bricks'],
                    ['block' => VanillaBlocks::CHISELED_STONE_BRICKS(), 'price' => 60, 'label' => 'Chiseled Stone Bricks'],
                    ['block' => VanillaBlocks::SANDSTONE(), 'price' => 80, 'label' => 'Sandstone'],
                    ['block' => VanillaBlocks::CHISELED_SANDSTONE(), 'price' => 80, 'label' => 'Chiseled Sandstone'],
                    ['block' => VanillaBlocks::SMOOTH_SANDSTONE(), 'price' => 80, 'label' => 'Smooth Sandstone'],
                    ['block' => VanillaBlocks::QUARTZ(), 'price' => 80, 'label' => 'Block of Quartz'],
                    ['block' => VanillaBlocks::CHISELED_QUARTZ(), 'price' => 80, 'label' => 'Chiseled Quartz Block'],
                    ['block' => VanillaBlocks::QUARTZ_PILLAR(), 'price' => 80, 'label' => 'Pillar Quartz'],
                    ['block' => VanillaBlocks::NETHERRACK(), 'price' => 20, 'label' => 'Netherrack'],
                    ['block' => VanillaBlocks::NETHER_BRICKS(), 'price' => 8, 'label' => 'Nether Brick'],
                    ['block' => VanillaBlocks::GLOWSTONE(), 'price' => 6, 'label' => 'Glowstone'],
                    ['block' => VanillaBlocks::SOUL_SAND(), 'price' => 6, 'label' => 'Soul Sand'],
                    ['block' => VanillaBlocks::END_STONE(), 'price' => 7, 'label' => 'End Stone'],
                    ['block' => VanillaBlocks::SAND(), 'price' => 3, 'label' => 'Sand'],
                    ['block' => VanillaBlocks::GRAVEL(), 'price' => 3, 'label' => 'Gravel'],
                    ['block' => VanillaBlocks::OBSIDIAN(), 'price' => 18, 'label' => 'Obsidian'],
                    ['block' => VanillaBlocks::ICE(), 'price' => 25, 'label' => 'Ice'],
                    ['block' => VanillaBlocks::PACKED_ICE(), 'price' => 50, 'label' => 'Packed Ice'],
                    ['block' => VanillaBlocks::SNOW(), 'price' => 250, 'label' => 'Snow'],
                    ['block' => VanillaBlocks::COBBLESTONE_STAIRS(), 'price' => 8, 'label' => 'Cobblestone Stairs'],
                    ['block' => VanillaBlocks::COBBLESTONE_WALL(), 'price' => 16, 'label' => 'Cobblestone Wall'],
                    ['block' => VanillaBlocks::OAK_FENCE_GATE(), 'price' => 10, 'label' => 'Oak Fence Gate'],
                    ['block' => VanillaBlocks::OAK_FENCE(), 'price' => 12, 'label' => 'Oak Fence'],
                    ['block' => VanillaBlocks::SPRUCE_WOOD(), 'price' => 8, 'label' => 'Spruce Wood'],
                    ['block' => VanillaBlocks::BRICKS(), 'price' => 8, 'label' => 'Bricks'],
                    ['block' => VanillaBlocks::CLAY(), 'price' => 7, 'label' => 'Clay'],
                    ['block' => VanillaBlocks::OAK_LEAVES(), 'price' => 6, 'label' => 'Oak Leaves'],
                    ['block' => VanillaBlocks::MOSSY_COBBLESTONE(), 'price' => 6, 'label' => 'Mossy Cobblestone'],
                    ['block' => VanillaBlocks::RED_SANDSTONE(), 'price' => 8, 'label' => 'Red Sandstone'],
                    ['block' => VanillaBlocks::RED_SAND(), 'price' => 3, 'label' => 'Red Sand'],
                    ['block' => VanillaBlocks::DARK_OAK_WOOD(), 'price' => 8, 'label' => 'Dark Oak Wood'],
                    ['block' => VanillaBlocks::OAK_SLAB(), 'price' => 80, 'label' => 'Oak Wood Slab'],
                ];
                
                $form = new SimpleForm(function(Player $player, $data) use ($session, $items): void {
                    if ($data === null) return;
                    if (isset($items[$data])) {
                        $item = $items[$data];
                        $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, $item['block']->asItem(), $item['price']));
                    }
                });
                
                $form->setTitle(C::colorize("&r&8Shop Items (Building Blocks)"));
                
                foreach ($items as $item) {
                    $form->addButton(C::colorize("&r&l&e{$item['label']}\n&r&aBuy Price: \${$item['price']}/ea"));
                }
                break;                
            case "ores_and_gems":
                $form = new SimpleForm(function(Player $player, $data): void {
                    if ($data === null) return;

                    switch ($data) {
                        case 0:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::IRON()->asItem(), 1080));
                            break;
                        case 1:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::IRON_INGOT(), 120));
                            break;
                        case 2:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::GOLD_INGOT(), 150));
                            break;
                        case 3:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::COAL(), 9));
                            break;
                        case 4:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::COAL()->asItem(), 80));    
                            break;
                        case 5:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::GOLD()->asItem(), 1350));
                            break;
                        case 6:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::LAPIS_LAZULI(), 18));
                            break;
                        case 7:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::LAPIS_LAZULI()->asItem(), 160));
                            break;
                        case 8:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::REDSTONE_DUST(), 19));
                            break;
                        case 9:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::REDSTONE()->asItem(), 195));
                            break;
                        case 10:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::DIAMOND(), 600));    
                            break;
                        case 11:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::DIAMOND_BLOCK()->asItem(), 5400));
                            break;
                        case 12:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::EMERALD(), 900));
                            break;
                        case 13:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::EMERALD_BLOCK()->asItem(), 8100));
                            break;
                    }
                });

                $form->setTitle(C::colorize("&r&8Ores and Gems"));
                $form->addButton(C::colorize("&r&l&eBlock of Iron\n&r&aBuy Price: $1080/ea"));
                $form->addButton(C::colorize("&r&l&eIron Ingot\n&r&aBuy Price: $120/ea"));
                $form->addButton(C::colorize("&r&l&eGold Ingot\n&r&aBuy Price: $150/ea"));
                $form->addButton(C::colorize("&r&l&eBlock of Coal\n&r&aBuy Price: $9/ea"));
                $form->addButton(C::colorize("&r&l&eCoal\n&r&aBuy Price: $80/ea"));
                $form->addButton(C::colorize("&r&l&eBlock of Gold\n&r&aBuy Price: $1350/ea"));
                $form->addButton(C::colorize("&r&l&eLapis Lazuli\n&r&aBuy Price: $18/ea"));
                $form->addButton(C::colorize("&r&l&eBlock of Lapis Lazuli\n&r&aBuy Price: $160/ea"));
                $form->addButton(C::colorize("&r&l&eRedstone Dust\n&r&aBuy Price: $19/ea"));
                $form->addButton(C::colorize("&r&l&eBlock of Redstone\n&r&aBuy Price: $195/ea"));
                $form->addButton(C::colorize("&r&l&eDiamond\n&r&aBuy Price: $600/ea"));
                $form->addButton(C::colorize("&r&l&eBlock of Diamond\n&r&aBuy Price: $5400/ea"));
                $form->addButton(C::colorize("&r&l&eEmerald\n&r&aBuy Price: $900/ea"));
                $form->addButton(C::colorize("&r&l&eBlock of Emerald\n&r&aBuy Price: $8100/ea"));
                break;
            case "food_and_farming":
                $form = new SimpleForm(function(Player $player, $data): void {
                    if ($data === null) return;

                    switch ($data) {
                        case 0:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::CACTUS()->asItem(), 11));
                            break;
                        case 1:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::SUGARCANE()->asItem(), 16));
                            break;
                        case 2:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::MELON(), 20));
                            break;
                        case 3:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::PUMPKIN()->asItem(), 325));
                            break;
                        case 4:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::WHEAT(), 7));
                            break;
                        case 5:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::APPLE(), 25));
                            break;
                        case 6:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::GOLDEN_APPLE(), 750));   
                            break;
                        case 7:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::CAKE()->asItem(), 50)); 
                            break;
                        case 8:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::BREAD(), 16));
                            break;
                        case 9:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::STEAK(), 10));
                            break;
                        case 10:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::COOKED_PORKCHOP(), 11));
                            break;
                        case 11:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::CARROT(), 25));
                            break;
                        case 12:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::POTATO(), 50));
                            break;
                    }
                });

                $form->setTitle(C::colorize("&r&8Food and Farming"));
                $form->addButton(C::colorize("&r&l&eCactus\n&r&aBuy Price: $11/ea"));
                $form->addButton(C::colorize("&r&l&eSugarcane\n&r&aBuy Price: $16/ea"));
                $form->addButton(C::colorize("&r&l&eMelon\n&r&aBuy Price: $20/ea"));
                $form->addButton(C::colorize("&r&l&ePumpkin\n&r&aBuy Price: $325/ea"));
                $form->addButton(C::colorize("&r&l&eWheat\n&r&aBuy Price: $7/ea"));
                $form->addButton(C::colorize("&r&l&eApple\n&r&aBuy Price: $25/ea"));
                $form->addButton(C::colorize("&r&l&eGolden Apple\n&r&aBuy Price: $750/ea"));
                $form->addButton(C::colorize("&r&l&eCake\n&r&aBuy Price: $50/ea"));
                $form->addButton(C::colorize("&r&l&eSteak\n&r&aBuy Price: $10/ea"));
                $form->addButton(C::colorize("&r&l&eCooked Porkchop\n&r&aBuy Price: $11/ea"));
                $form->addButton(C::colorize("&r&l&eCarrot\n&r&aBuy Price: $25/ea"));
                $form->addButton(C::colorize("&r&l&ePotato\n&r&aBuy Price: $50/ea"));
                break;
            case "mob_drops":
                $form = new SimpleForm(function(Player $player, $data): void {
                    if ($data === null) return;

                    switch ($data) {
                        case 0:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::GUNPOWDER(), 25));
                            break;
                        case 1:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::ARROW(), 5));
                            break;
                        case 2:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::FEATHER(), 8));
                            break;
                        case 3:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::BLAZE_ROD(), 50));
                            break;
                        case 4:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::LILY_PAD()->asItem(), 20));
                            break;
                        case 5:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::ROTTEN_FLESH(), 3));
                            break;
                        case 6:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::STRING(), 8));
                            break;
                        case 7:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::LEATHER(), 12));
                            break;
                        case 8:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::ENDER_PEARL(), 100));
                            break;
                        case 9:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::BONE(), 6));
                            break;
                    }
                });

                $form->setTitle(C::colorize("&r&8Mob Drops"));

                $form->addButton(C::colorize("&r&l&eGunpowder\n&r&aBuy Price: $25/ea"));
                $form->addButton(C::colorize("&r&l&eArrow\n&r&aBuy Price: $5/ea"));
                $form->addButton(C::colorize("&r&l&eFeather\n&r&aBuy Price: $8/ea"));
                $form->addButton(C::colorize("&r&l&eBlaze Rod\n&r&aBuy Price: $50/ea"));
                $form->addButton(C::colorize("&r&l&eLily Pad\n&r&aBuy Price: $20/ea"));
                $form->addButton(C::colorize("&r&l&eRotten Flesh\n&r&aBuy Price: $3/ea"));
                $form->addButton(C::colorize("&r&l&eString\n&r&aBuy Price: $8/ea"));
                $form->addButton(C::colorize("&r&l&eLeather\n&r&aBuy Price: $12/ea"));
                $form->addButton(C::colorize("&r&l&eEnder Pearl\n&r&aBuy Price: $100/ea"));
                $form->addButton(C::colorize("&r&l&eBone\n&r&aBuy Price: $6/ea"));
                break;
            case "speciality":
                $form = new SimpleForm(function(Player $player, $data): void {
                    if ($data === null) return;

                    switch ($data) {
                        case 0:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::CHEST()->asItem(), 500));
                            break;
                        case 1:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::TRAPPED_CHEST()->asItem(), 1000));
                            break;
                        case 2:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::ENCHANTING_TABLE()->asItem(), 10000));
                            break;
                        case 3:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::ANVIL()->asItem(), 1500));
                            break;
                        case 4:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::ENDER_CHEST()->asItem(), 100430));
                            break;
                        case 5:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::BEACON()->asItem(), 250000));
                            break;
                        case 6:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::FLINT_AND_STEEL(), 10000));
                            break;
                        case 7:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::DAYLIGHT_SENSOR()->asItem(), 640));
                            break;
                        case 8:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::TRIPWIRE_HOOK()->asItem(), 80));
                            break;
                        case 9:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("sticky_piston"), 325));
                            break;
                        case 10:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("piston"), 195));
                            break;
                        case 11:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::REDSTONE_LAMP()->asItem(), 730));
                            break;
                        case 12:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::COBWEB()->asItem(), 150));
                            break;
                        case 13:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, StringToItemParser::getInstance()->parse("dispenser"), 2000));
                            break;
                        case 14:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::ICE()->asItem(), 250));
                            break;
                        case 15:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::PACKED_ICE()->asItem(), 75));
                            break;
                        case 16:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::SPONGE()->asItem(), 2500));
                            break;
                        case 17:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::FISHING_ROD(), 750));
                            break;
                    }
                });

                $form->setTitle(C::colorize("&r&8Speciality"));

                $form->addButton(C::colorize("&r&l&eChest\n&r&aBuy Price: $500/ea"));
                $form->addButton(C::colorize("&r&l&eTrapped Chest\n&r&aBuy Price: $1000/ea"));
                $form->addButton(C::colorize("&r&l&eEnchanting Table\n&r&aBuy Price: $10000/ea"));
                $form->addButton(C::colorize("&r&l&eAnvil\n&r&aBuy Price: $1500/ea"));
                $form->addButton(C::colorize("&r&l&eEnder Chest\n&r&aBuy Price: $100430/ea"));
                $form->addButton(C::colorize("&r&l&eBeacon\n&r&aBuy Price: $250000/ea"));
                $form->addButton(C::colorize("&r&l&eFlint and Steel\n&r&aBuy Price: $10000/ea"));
                $form->addButton(C::colorize("&r&l&eDaylight Sensor\n&r&aBuy Price: $640/ea"));
                $form->addButton(C::colorize("&r&l&eTripwire Hook\n&r&aBuy Price: $80/ea"));
                $form->addButton(C::colorize("&r&l&eSticky Piston\n&r&aBuy Price: $325/ea"));
                $form->addButton(C::colorize("&r&l&ePiston\n&r&aBuy Price: $195/ea"));
                $form->addButton(C::colorize("&r&l&eRedstone Lamp\n&r&aBuy Price: $730/ea"));
                $form->addButton(C::colorize("&r&l&eCobweb\n&r&aBuy Price: $150/ea"));
                $form->addButton(C::colorize("&r&l&eDispenser\n&r&aBuy Price: $2000/ea"));
                $form->addButton(C::colorize("&r&l&eIce\n&r&aBuy Price: $250/ea"));
                $form->addButton(C::colorize("&r&l&ePacked Ice\n&r&aBuy Price: $75/ea"));
                $form->addButton(C::colorize("&r&l&eSponge\n&r&aBuy Price: $2500/ea"));
                $form->addButton(C::colorize("&r&l&eFishing Rod\n&r&aBuy Price: $750/ea")); 
                break;
            case "brewing":
                $form = new SimpleForm(function(Player $player, $data): void {
                    if ($data === null) return;

                    switch ($data) {
                        case 0:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::SPIDER_EYE(), 130));
                            break;
                        case 1:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::GHAST_TEAR(), 97));   
                            break;
                        case 2:
                             $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::BREWING_STAND()->asItem(), 315));
                            break;
                        case 3:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::GLASS_BOTTLE(), 7));
                            break;
                        case 4:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::GOLDEN_CARROT(), 73));
                            break;
                        case 5:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::GLOWSTONE_DUST(), 5));
                            break;
                        case 6:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::SUGAR(), 5));
                            break;
                        case 7:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::SLIMEBALL(), 200));
                            break;
                        case 8:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::NETHER_WART()->asItem(), 14));
                            break;
                        case 9:
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::GLISTERING_MELON(), 34));
                            break;
                    }
                });

                $form->setTitle(C::colorize("&r&8Brewing"));

                $form->addButton(C::colorize("&r&l&eSpider Eye\n&r&aBuy Price: $130/ea"));
                $form->addButton(C::colorize("&r&l&eGhast Tear\n&r&aBuy Price: $97/ea"));
                $form->addButton(C::colorize("&r&l&eBrewing Stand\n&r&aBuy Price: $315/ea"));
                $form->addButton(C::colorize("&r&l&eGlass Bottle\n&r&aBuy Price: $7/ea"));
                $form->addButton(C::colorize("&r&l&eGolden Carrot\n&r&aBuy Price: $73/ea"));
                $form->addButton(C::colorize("&r&l&eGlowstone Dust\n&r&aBuy Price: $5/ea"));
                $form->addButton(C::colorize("&r&l&eSugar\n&r&aBuy Price: $5/ea"));
                $form->addButton(C::colorize("&r&l&eSlimeball\n&r&aBuy Price: $200/ea"));
                $form->addButton(C::colorize("&r&l&eNether Wart\n&r&aBuy Price: $14/ea"));
                $form->addButton(C::colorize("&r&l&eGlistering Melon\n&r&aBuy Price: $34/ea"));
                break;
            case "wool":
                $colors = [
                    "White" => DyeColor::WHITE(),
                    "Orange" => DyeColor::ORANGE(),
                    "Magenta" => DyeColor::MAGENTA(),
                    "Light Blue" => DyeColor::LIGHT_BLUE(),
                    "Yellow" => DyeColor::YELLOW(),
                    "Lime" => DyeColor::LIME(),
                    "Pink" => DyeColor::PINK(),
                    "Gray" => DyeColor::GRAY(),
                    "Light Gray" => DyeColor::LIGHT_GRAY(),
                    "Cyan" => DyeColor::CYAN(),
                    "Purple" => DyeColor::PURPLE(),
                    "Blue" => DyeColor::BLUE(),
                    "Brown" => DyeColor::BROWN(),
                    "Green" => DyeColor::GREEN(),
                    "Red" => DyeColor::RED(),
                    "Black" => DyeColor::BLACK(),
                ];

                $form = new SimpleForm(function(Player $player, $data) use ($colors): void {
                    if ($data === null) return;
            
                    $i = 0;
                    foreach ($colors as $colorName => $dyeColor) {
                        if ($data === $i) {
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::WOOL()->setColor($dyeColor)->asItem(), 2));
                            return;
                        }
                        $i++;
                        if ($data === $i) {
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::CARPET()->setColor($dyeColor)->asItem(), 1));
                            return;
                        }
                        $i++;
                    }
                });
            

                $form->setTitle(C::colorize("&r&8Wool"));

                foreach ($colors as $colorName => $dyeColor) {
                    $form->addButton(C::colorize("&r&l&e{$colorName} Wool\n&r&aBuy Price: $2/ea"));
                    $form->addButton(C::colorize("&r&l&e{$colorName} Carpet\n&r&aBuy Price: $1/ea"));
                }
                break;
            case "glass":
                $colors = [
                    "White" => DyeColor::WHITE(),
                    "Orange" => DyeColor::ORANGE(),
                    "Magenta" => DyeColor::MAGENTA(),
                    "Light Blue" => DyeColor::LIGHT_BLUE(),
                    "Yellow" => DyeColor::YELLOW(),
                    "Lime" => DyeColor::LIME(),
                    "Pink" => DyeColor::PINK(),
                    "Gray" => DyeColor::GRAY(),
                    "Light Gray" => DyeColor::LIGHT_GRAY(),
                    "Cyan" => DyeColor::CYAN(),
                    "Purple" => DyeColor::PURPLE(),
                    "Blue" => DyeColor::BLUE(),
                    "Brown" => DyeColor::BROWN(),
                    "Green" => DyeColor::GREEN(),
                    "Red" => DyeColor::RED(),
                    "Black" => DyeColor::BLACK(),
                ];

                $form = new SimpleForm(function(Player $player, $data) use ($colors): void {
                    if ($data === null) return;
            
                    $i = 0;
                    foreach ($colors as $colorName => $dyeColor) {
                        if ($data === $i) {
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::GLASS()->asItem(), 4));
                            return;
                        }
                        $i++;
                        if ($data === $i) {
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::GLASS_PANE()->asItem(), 1));
                            return;
                        }
                        $i++;
                    
                        foreach ($colors as $colorName => $dyeColor) {
                            if ($data === $i) {
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::STAINED_GLASS()->setColor($dyeColor)->asItem(), 4));
                                return;
                            }
                            $i++;
                            if ($data === $i) {
                                $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::STAINED_GLASS_PANE()->setColor($dyeColor)->asItem(), 1));
                                return;
                            }
                            $i++;
                        }
                    }
                });
            

                $form->setTitle(C::colorize("&r&8Glass"));
                $form->addButton(C::colorize("&r&l&eGlass\n&r&aBuy Price: $4/ea"));
                $form->addButton(C::colorize("&r&l&eGlass Pane\n&r&aBuy Price: $1/ea"));

                foreach ($colors as $colorName => $dyeColor) {
                    $form->addButton(C::colorize("&r&l&e{$colorName} Stained Glass\n&r&aBuy Price: $4/ea"));
                    $form->addButton(C::colorize("&r&l&e{$colorName} Stained Glass Pane\n&r&aBuy Price: $1/ea"));
                }
                break;
            case "base_grind":

                break;
            case "clay":
                $colors = [
                    "White" => DyeColor::WHITE(),
                    "Orange" => DyeColor::ORANGE(),
                    "Magenta" => DyeColor::MAGENTA(),
                    "Light Blue" => DyeColor::LIGHT_BLUE(),
                    "Yellow" => DyeColor::YELLOW(),
                    "Lime" => DyeColor::LIME(),
                    "Pink" => DyeColor::PINK(),
                    "Gray" => DyeColor::GRAY(),
                    "Light Gray" => DyeColor::LIGHT_GRAY(),
                    "Cyan" => DyeColor::CYAN(),
                    "Purple" => DyeColor::PURPLE(),
                    "Blue" => DyeColor::BLUE(),
                    "Brown" => DyeColor::BROWN(),
                    "Green" => DyeColor::GREEN(),
                    "Red" => DyeColor::RED(),
                    "Black" => DyeColor::BLACK(),
                ];
                
                $form = new SimpleForm(function(Player $player, $data) use ($colors): void {
                    if ($data === null) return;
                
                    $i = 0;
                
                    if ($data === $i) {
                        $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::CLAY()->asItem(), 6));
                        return;
                    }
                    $i++;
                
                    foreach ($colors as $colorName => $dyeColor) {
                        if ($data === $i) {
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaBlocks::STAINED_HARDENED_CLAY()->setColor($dyeColor)->asItem(), 6));
                            return;
                        }
                        $i++;
                    }
                });
                
                $form->setTitle(C::colorize("&r&8Clay"));
                
                $form->addButton(C::colorize("&r&l&eNormal Clay\n&r&aBuy Price: $6/ea"));
                
                foreach ($colors as $colorName => $dyeColor) {
                    $form->addButton(C::colorize("&r&l&e{$colorName} Stained Clay\n&r&aBuy Price: $6/ea"));
                }
                break;
            case "flowers":
                $flowers = [
                    "Dandelion" => VanillaBlocks::DANDELION()->asItem(),
                    "Poppy" => VanillaBlocks::POPPY()->asItem(),
                    "Blue Orchid" => VanillaBlocks::BLUE_ORCHID()->asItem(),
                    "Allium" => VanillaBlocks::ALLIUM()->asItem(),
                    "Azure Bluet" => VanillaBlocks::AZURE_BLUET()->asItem(),
                    "Red Tulip" => VanillaBlocks::RED_TULIP()->asItem(),
                    "Orange Tulip" => VanillaBlocks::ORANGE_TULIP()->asItem(),
                    "White Tulip" => VanillaBlocks::WHITE_TULIP()->asItem(),
                    "Pink Tulip" => VanillaBlocks::PINK_TULIP()->asItem(),
                    "Oxeye Daisy" => VanillaBlocks::OXEYE_DAISY()->asItem(),
                    "Cornflower" => VanillaBlocks::CORNFLOWER()->asItem(),
                    "Lily of the Valley" => VanillaBlocks::LILY_OF_THE_VALLEY()->asItem(),
                    "Wither Rose" => VanillaBlocks::WITHER_ROSE()->asItem(),
                    "Sunflower" => VanillaBlocks::SUNFLOWER()->asItem(),
                    "Lilac" => VanillaBlocks::LILAC()->asItem(),
                    "Rose Bush" => VanillaBlocks::ROSE_BUSH()->asItem(),
                    "Peony" => VanillaBlocks::PEONY()->asItem(),
                ];
        
                $form = new SimpleForm(function(Player $player, $data) use ($flowers): void {
                    if ($data === null) return;
        
                    $i = 0;
        
                    foreach ($flowers as $flowerName => $flowerItem) {
                        if ($data === $i) {
                            $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, $flowerItem, 18));
                            return;
                        }
                        $i++;
                    }
        
                    if ($data === $i) {
                        $player->sendForm(ElysiumUtils::sendPurchaseConfirmation($player, VanillaItems::FLOWER_POT(), 300));
                    }
                });
        
                $form->setTitle(C::colorize("&r&8Flowers"));
        
                foreach ($flowers as $flowerName => $flowerItem) {
                    $form->addButton(C::colorize("&r&l&e{$flowerName}\n&r&aBuy Price: $18/ea"));
                }
        
                $form->addButton(C::colorize("&r&l&eFlower Pot\n&r&aBuy Price: $300/ea"));
                
        
                break;
            
        }
        return $form;
    }

    public static function getShopCategoriesMenu(Player $player): InvMenu {
        $menu = CustomSizedInvMenu::create(18);
        $inventory = $menu->getInventory();

        $menu->setName(C::colorize("&r&8Shop Categories"));
        
        $categories = [
            ['index' => 0, 'item' => VanillaItems::POTION()->setCustomName(C::colorize("&r&l&ePotion Shop"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("potion", $player)],
            ['index' => 1, 'item' => VanillaItems::REDSTONE_DUST()->setCustomName(C::colorize("&r&l&eRaid Shop"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("raid", $player)],
            ['index' => 2, 'item' => VanillaBlocks::MONSTER_SPAWNER()->asItem()->setCustomName(C::colorize("&r&l&eSpawners Shop"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("spawners", $player)],
            ['index' => 3, 'item' => VanillaBlocks::STONE_BRICKS()->asItem()->setCustomName(C::colorize("&r&l&eBuilding Blocks Shop"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("building_blocks", $player)],
            ['index' => 4, 'item' => VanillaItems::EMERALD()->setCustomName(C::colorize("&r&l&eOres and Gems"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("ores_and_gems", $player)],
            ['index' => 5, 'item' => VanillaItems::STEAK()->setCustomName(C::colorize("&r&l&eFood and Farming"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("food_and_farming", $player)],
            ['index' => 6, 'item' => VanillaItems::ARROW()->setCustomName(C::colorize("&r&l&eMob Drops"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("mob_drops", $player)],
            ['index' => 7, 'item' => StringToItemParser::getInstance()->parse("ender_eye")->setCustomName(C::colorize("&r&l&eSpeciality"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("speciality", $player)],
            ['index' => 8, 'item' => VanillaBlocks::BREWING_STAND()->asItem()->setCustomName(C::colorize("&r&l&eBrewing"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("brewing", $player)],
            ['index' => 11, 'item' => VanillaBlocks::WOOL()->asItem()->setCustomName(C::colorize("&r&l&eWool"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("wool", $player)],
            ['index' => 12, 'item' => VanillaBlocks::GLASS()->asItem()->setCustomName(C::colorize("&r&l&eGlass"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("glass", $player)],
            ['index' => 13, 'item' => VanillaBlocks::OBSIDIAN()->asItem()->setCustomName(C::colorize("&r&l&eBase Grind"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("base_grind", $player)],
            ['index' => 14, 'item' => VanillaBlocks::CLAY()->asItem()->setCustomName(C::colorize("&r&l&eClay"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("clay", $player)],
            ['index' => 15, 'item' => VanillaBlocks::POPPY()->asItem()->setCustomName(C::colorize("&r&l&eFlowers"))->setLore([C::colorize("&r&7Click to view this category.")]), 'shop' => self::getShopCategoryForm("flowers", $player)],
        ];
    
        foreach ($categories as $category) {
            $index = $category['index'];
            $item = $category['item'];
            $inventory->setItem($index, $item);
        }
    
        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use ($categories) {
            $index = $transaction->getAction()->getSlot();
            if (isset($categories[$index])) {
                $category = $categories[$index];
                $shopMenu = $category['shop'];
                $transaction->getPlayer()->removeCurrentWindow();
                $transaction->getPlayer()->sendForm($shopMenu);
            }
        }));
        
        return $menu;
    }
}
