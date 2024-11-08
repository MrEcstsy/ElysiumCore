<?php

namespace xtcy\ElysiumCore;

use cosmicpe\blockdata\world\BlockDataWorldManager;
use DaPigGuy\PiggyFactions\factions\FactionsManager;
use DaPigGuy\PiggyFactions\PiggyFactions;
use DaPigGuy\PiggyFactions\players\FactionsPlayer;
use DaPigGuy\PiggyFactions\players\PlayerManager as FactionPlayerManager;
use DaPigGuy\PiggyFactions\utils\Relations;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\tag\Tag;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\World;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Wertzui123\CBHeads\Main;
use xtcy\ElysiumCore\addons\regions\Region;
use xtcy\ElysiumCore\addons\regions\RegionManager;
use xtcy\ElysiumCore\addons\scorehud\TagResolveListener;
use xtcy\ElysiumCore\commands\balance\AddBalanceCommand;
use xtcy\ElysiumCore\commands\balance\BalanceCommand;
use xtcy\ElysiumCore\commands\balance\BalanceTopCommand;
use xtcy\ElysiumCore\commands\balance\PayCommand;
use xtcy\ElysiumCore\commands\balance\SetBalanceCommand;
use xtcy\ElysiumCore\commands\balance\TakeBalanceCommand;
use xtcy\ElysiumCore\commands\balance\WithdrawCommand;
use xtcy\ElysiumCore\commands\BroadcastCommand;
use xtcy\ElysiumCore\commands\CoordinateCommand;
use xtcy\ElysiumCore\commands\CreateWarpCommand;
use xtcy\ElysiumCore\commands\CustomEnchantsCommand;
use xtcy\ElysiumCore\commands\EnchanterCommand;
use xtcy\ElysiumCore\commands\exp\ExperienceCommand;
use xtcy\ElysiumCore\commands\FeedCommand;
use xtcy\ElysiumCore\commands\FixCommand;
use xtcy\ElysiumCore\commands\FlyCommand;
use xtcy\ElysiumCore\commands\gems\GemCommand;
use xtcy\ElysiumCore\commands\GiveMaxHomeCommand;
use xtcy\ElysiumCore\commands\GiveVoteKeyCommand;
use xtcy\ElysiumCore\commands\HealCommand;
use xtcy\ElysiumCore\commands\HomeCommand;
use xtcy\ElysiumCore\commands\HomesCommand;
use xtcy\ElysiumCore\commands\KitCommand;
use xtcy\ElysiumCore\commands\ListWarpsCommand;
use xtcy\ElysiumCore\commands\QuestCommand;
use xtcy\ElysiumCore\commands\RemoveHomeCommand;
use xtcy\ElysiumCore\commands\RemoveWarpCommand;
use xtcy\ElysiumCore\commands\SeeBragCommand;
use xtcy\ElysiumCore\commands\SellCommand;
use xtcy\ElysiumCore\commands\SetHomeCommand;
use xtcy\ElysiumCore\commands\SettingsCommand;
use xtcy\ElysiumCore\commands\ShopCommand;
use xtcy\ElysiumCore\commands\SlotBotCommand;
use xtcy\ElysiumCore\commands\SpawnCommand;
use xtcy\ElysiumCore\commands\TicketsCommand;
use xtcy\ElysiumCore\commands\TitlesCommand;
use xtcy\ElysiumCore\commands\TpAcceptCommand;
use xtcy\ElysiumCore\commands\TpaCommand;
use xtcy\ElysiumCore\commands\TpaHereCommand;
use xtcy\ElysiumCore\commands\TPAllCommand;
use xtcy\ElysiumCore\commands\TpDenyCommand;
use xtcy\ElysiumCore\commands\WarpCommand;
use xtcy\ElysiumCore\enchants\util\CustomEnchantments;
use xtcy\ElysiumCore\enchants\vanilla\DepthStriderEnchantment;
use xtcy\ElysiumCore\enchants\vanilla\LootingEnchantment;
use xtcy\ElysiumCore\entities\AncientGuardian;
use xtcy\ElysiumCore\entities\AstralCrystal;
use xtcy\ElysiumCore\entities\AstralRanger;
use xtcy\ElysiumCore\entities\BroodMother;
use xtcy\ElysiumCore\entities\FloatingTextEntity;
use xtcy\ElysiumCore\entities\HollowCrystal;
use xtcy\ElysiumCore\entities\HollowGuardian;
use xtcy\ElysiumCore\entities\SoulCrystal;
use xtcy\ElysiumCore\entities\SoulGuardian;
use xtcy\ElysiumCore\listeners\CrateListener;
use xtcy\ElysiumCore\listeners\EnchantListener;
use xtcy\ElysiumCore\listeners\EventListener;
use xtcy\ElysiumCore\listeners\ItemListener;
use xtcy\ElysiumCore\listeners\QuestListener;
use xtcy\ElysiumCore\listeners\RegionListener;
use xtcy\ElysiumCore\player\homes\HomeManager;
use xtcy\ElysiumCore\player\PlayerManager;
use xtcy\ElysiumCore\server\Warps\WarpManager;
use xtcy\ElysiumCore\tasks\SpawnEnvoyTask;
use xtcy\ElysiumCore\utils\ElysiumUtils;
use xtcy\ElysiumCore\utils\Queries;

class Loader extends PluginBase {

    use SingletonTrait;

    public const SERVER_NAME = "&r&d&k:&r&l&dELYSIUM&fREALM&r&d&k:&r";

    private static DataConnector $connector;

    private static PlayerManager $playerManager;

    private static HomeManager $homeManager;

    private static WarpManager $warpManager;

    private array $rankSymbols;

    public function onLoad(): void
    {
        self::setInstance($this);
        $enchants = [
        new LootingEnchantment(),
        new DepthStriderEnchantment(),
    ];
        foreach ($enchants as $enchant) {
            EnchantmentIdMap::getInstance()->register($enchant->getMcpeId(), $enchant);
            StringToEnchantmentParser::getInstance()->register($enchant->getId(), fn() => $enchant);
        }

        $this->getServer()->getWorldManager()->loadWorld("flat");
    }

    public function onEnable(): void
    {
        $this->init();
    }

    public function init(): void
    {
        $this->getServer()->getNetwork()->setName(C::colorize(self::SERVER_NAME));
        $this->saveDefaultConfig();

        $settings = [
            "type" => "sqlite",
            "sqlite" => ["file" => "sqlite.sql"],
            "worker-limit" => 2
        ];
        self::$connector = libasynql::create($this, $settings, ["sqlite" => "sqlite.sql"]);

        self::$connector->executeGeneric(Queries::PLAYERS_INIT);
        self::$connector->executeGeneric(Queries::HOMES_INIT);
        self::$connector->executeGeneric(Queries::WARPS_INIT);

        self::$connector->waitAll();

        self::$playerManager = new PlayerManager($this);
        self::$homeManager = new HomeManager($this, 3);
        self::$warpManager = new WarpManager($this);

        $cmds = ["title"];

        foreach ($cmds as $cmd) {
            $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand($cmd));

        }

        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        $this->getServer()->getCommandMap()->registerAll("ElysiumCore", [
            new TPAllCommand($this, "tpall", "Teleport all online players"),
            new CoordinateCommand($this, "coordinate", "Show coordinates", ["cords"]),
            new BalanceCommand($this, "balance", "View your balance", ["bal"]),
            new GemCommand($this, "gems", "View your gems", ["gem"]),
            new SettingsCommand($this, "settings", "View your settings", ["set"]),
            new KitCommand($this, "kit", "View your kits", ["kits"]),
            new CustomEnchantsCommand($this, "customenchants", "View custom enchantments", ["ce"]),
            new ExperienceCommand($this, "exp", "View your experience", ["xp"]),
            new WithdrawCommand($this, "withdraw", "Withdraw your balance"),
            new ShopCommand($this, "shop", "View the shop"),
            new SlotBotCommand($this, "slotbot", "View the slot menu", ["slot", "bot"]),
            new TicketsCommand($this, "tickets", "Give tickets to a player", ["ticket"]),
            new TpaCommand($this, "tpa", "Teleport to a player"),
            new TpAcceptCommand($this, "tpaccept", "Accept a teleport request", ["tpyes"]),
            new TpDenyCommand($this, "tpdeny", "Deny a teleport request", ["tpno"]),
            new HomeCommand($this, "home", "Teleport to your home"),
            new HomesCommand($this, "homes", "View your homes"),
            new SetHomeCommand($this, "sethome", "Set your home"),
            new RemoveHomeCommand($this, "removehome", "Remove your home", ["delhome"]),
            new QuestCommand($this, "quest", "View your quests", ["quests"]),
            new SeeBragCommand($this, "seebrag", "View a players brag", ["cbrag"]),
            new SpawnCommand($this, "spawn", "Teleport to spawn"),
            new TitlesCommand($this, "titles", "View your titles", ["title"]),
            new AddBalanceCommand($this, "addbalance", "Add balance to a player", ["addbal"]),
            new SetBalanceCommand($this, "setbalance", "Set balance of a player", ["setbal"]),
            new TakeBalanceCommand($this, "takebalance", "Take balance from a player", ["takebal"]),
            new GiveVoteKeyCommand($this, "givevotekey", "Give vote key", ["gvk"]),
            new BalanceTopCommand($this, "balancetop", "View balance leaderboard", ["baltop"]),
            new PayCommand($this, "pay", "Pay a player"),
            new GiveMaxHomeCommand($this, "givemaxhome", "Give max home", ["maxhome"]),
            new BroadcastCommand($this, "broadcast", "Broadcast message"),
            new SellCommand($this, "sell", "Sell items"),
            new WarpCommand($this, "warp", "Teleport to a warp"),
            new ListWarpsCommand($this, "listwarps", "List warps", ["warps"]),
            new CreateWarpCommand($this, "createwarp", "Create a warp", ["setwarp"]),
            new RemoveWarpCommand($this, "removewarp", "Remove a warp", ["delwarp"]),
            new TpaHereCommand($this, "tpahere", "Teleport a player to where you are standing"),
            new HealCommand($this, "heal", "Heal yourself"),
            new FeedCommand($this, "feed", "Feed yourself"),
            new FlyCommand($this, "fly", "Allows the user to fly"),
            new EnchanterCommand($this, "enchanter", "Purchase custom enchantments"),
            new FixCommand($this, "fix", "Fix your inventory"),
        ]);

        $regionManager = new RegionManager();

        $listeners = [
            new EventListener($regionManager),
            new CrateListener(),
            new ItemListener($regionManager),
            new EnchantListener(),
            new TagResolveListener($this),
            new RegionListener($regionManager),
            new QuestListener()
        ];
        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }

        CustomEnchantments::getAll();

        EntityFactory::getInstance()->register(BroodMother::class, function(World $world, CompoundTag $nbt): Entity {
            return new BroodMother(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [BroodMother::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(AncientGuardian::class, function(World $world, CompoundTag $nbt): Entity {
            return new AncientGuardian(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [AncientGuardian::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(FloatingTextEntity::class, function(World $world, CompoundTag $nbt): Entity {
            return new FloatingTextEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [FloatingTextEntity::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(AstralCrystal::class, function(World $world, CompoundTag $nbt): Entity {
            return new AstralCrystal(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [AstralCrystal::getNetworkTypeId()]);
        
        EntityFactory::getInstance()->register(AstralRanger::class, function(World $world, CompoundTag $nbt): Entity {
            return new AstralRanger(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [AstralRanger::getNetworkTypeId()]);
        
        EntityFactory::getInstance()->register(HollowGuardian::class, function(World $world, CompoundTag $nbt): Entity {
            return new HollowGuardian(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [HollowGuardian::getNetworkTypeId()]);

        EntityFactory::getInstance()->register(SoulGuardian::class, function(World $world, CompoundTag $nbt): Entity {
            return new SoulGuardian(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [SoulGuardian::getNetworkTypeId()]);
  
        EntityFactory::getInstance()->register(HollowCrystal::class, function(World $world, CompoundTag $nbt): Entity {
            return new HollowCrystal(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [HollowCrystal::getNetworkTypeId()]);
        

        EntityFactory::getInstance()->register(SoulCrystal::class, function(World $world, CompoundTag $nbt): Entity {
            return new SoulCrystal(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, [SoulCrystal::getNetworkTypeId()]);

        $regions = [
            new Region("Spawn", new Vector3(-206, 94, 14), new Vector3(-31, 250, 250), false, false, true, false, false, false, false),
            new Region("Warzone", new Vector3(-232, 1, -1), new Vector3(0, 250, 220), true, false, true, false, false, false, false)
        ];
        
        foreach ($regions as $region) {
            $regionManager->addRegion($region);
        }

        RankSystem::getInstance()->getTagManager()->registerTag(new Tag("title", function(Session $user): string {
            return Loader::getPlayerManager()->getSession($user->getPlayer())->getTitle();
        }));

        RankSystem::getInstance()->getTagManager()->registerTag(new Tag("fac_name", function(Session $user): string {
            $p = $user->getPlayer();
            $fP = FactionPlayerManager::getInstance()->getPlayer($p);
            
            if ($fP->getFaction() !== null) {
                $faction = $fP->getFaction();
                $playerFaction = PiggyFactions::getInstance()->getPlayerManager()->getPlayer($p)->getFaction();
        
                if ($playerFaction !== null) {
                    $relation = $playerFaction->getRelation($faction);
        
                    switch ($relation) {
                        case Relations::ALLY:
                            return C::LIGHT_PURPLE . $faction->getName(); 
                        case Relations::TRUCE:
                            return C::AQUA . $faction->getName(); 
                        case Relations::ENEMY:
                            return C::RED . $faction->getName(); 
                        default:
                            return C::WHITE . $faction->getName(); 
                    }
                } else {
                    return C::WHITE . $faction->getName(); 
                }
            } else {
                return ""; 
            }
        }));
        
        RankSystem::getInstance()->getTagManager()->registerTag(new Tag("fac_rank", function(Session $user): string {
            $p = $user->getPlayer();
            $facPlayer = PiggyFactions::getInstance()->getPlayerManager()->getPlayer($p);
            $fac = $facPlayer->getFaction();
        
            if ($fac === null) {
                return ""; 
            }
        
            $playerFaction = PiggyFactions::getInstance()->getPlayerManager()->getPlayer($p)->getFaction();
        
            if ($playerFaction !== null) {
                if ($playerFaction->getId() === $fac->getId()) {
                    return C::GREEN . ElysiumUtils::getSymbol($facPlayer->getRole()); 
                } else {
                    $relation = $playerFaction->getRelation($fac);
        
                    switch ($relation) {
                        case Relations::ALLY:
                            return C::LIGHT_PURPLE . ElysiumUtils::getSymbol($facPlayer->getRole());
                        case Relations::TRUCE:
                            return C::AQUA . ElysiumUtils::getSymbol($facPlayer->getRole());
                        case Relations::ENEMY:
                            return C::RED . ElysiumUtils::getSymbol($facPlayer->getRole()); 
                        default:
                            return C::WHITE . ElysiumUtils::getSymbol($facPlayer->getRole()); 
                    }
                }
            } else {
                return C::WHITE . ElysiumUtils::getSymbol($facPlayer->getRole()); 
            }
        }));
        
        
        $this->saveResourceFiles('dp');
    }
    
    public static function getDataBase(): DataConnector
    {
        return self::$connector;
    }

    public static function getPlayerManager(): PlayerManager
    {
        return self::$playerManager;
    }

    public static function getHomeManager(): HomeManager
    {
        return self::$homeManager;
    }

    public static function getWarpManager(): WarpManager {
        return self::$warpManager;
    }

    private function saveResourceFiles(string $resourceDir): void {
        $resourcePath = $this->getFile() . "resources/" . $resourceDir . "/";
        $targetPath = $this->getDataFolder() . $resourceDir . "/";
    
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }
    
        foreach (glob($resourcePath . '*.yml') as $file) {
            $fileName = basename($file);
            $this->saveResource($resourceDir . '/' . $fileName, false);
        }
    }

}