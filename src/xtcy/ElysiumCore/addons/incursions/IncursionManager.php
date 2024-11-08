<?php

namespace xtcy\ElysiumCore\addons\incursions;

use pocketmine\entity\Location;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\ChunkLoader;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use xtcy\ElysiumCore\entities\AstralCrystal;
use xtcy\ElysiumCore\entities\AstralRanger;
use xtcy\ElysiumCore\entities\HollowCrystal;
use xtcy\ElysiumCore\entities\HollowGuardian;
use xtcy\ElysiumCore\entities\SoulCrystal;
use xtcy\ElysiumCore\entities\SoulGuardian;
use xtcy\ElysiumCore\Loader;

class IncursionManager {

    public static array $incursions = [];

    public static function scheduleIncursions(): void {
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new class() extends Task {
            public function onRun(): void {
                IncursionManager::spawnRandomIncursion();
            }
        }, mt_rand(3600, 7200) * 20);
    }

    public static function spawnRandomIncursion(): void {
        $incursionTypes = ["soul", "hollow", "astral"];
        $incursionType = $incursionTypes[array_rand($incursionTypes)];

        self::getRandomPosition(function (Position $position) use ($incursionType) {
            $players = Server::getInstance()->getOnlinePlayers();

            foreach ($players as $player) {
                switch ($incursionType) {
                    case "soul":
                        $player->sendMessage(C::colorize("          &r&l&f<&r&3Incursion&f: &4Soul&l&f>"));
                        $player->sendMessage(C::colorize("&r&7&o A dimensional rift has torn open, unleashing the holy"));
                        $player->sendMessage(C::colorize("&r&7&o    forces of the Soul Guardians upon the realm."));
                        $player->sendMessage(C::colorize("&r&7&o        They have come to invade and conquer!"));
                        $player->sendMessage(C::colorize("&r&f➥ &lSpawned at&r&f: &b" . $position->getFloorX() . "&r&f, &b" . $position->getFloorZ()));
                        break;
                    case "hollow":
                        $player->sendMessage(C::colorize("          &r&l&f<&r&3Incursion&f: &fHollow&l&f>"));
                        $player->sendMessage(C::colorize("&r&7&o The fabric of reality shivers as a rift opens,"));
                        $player->sendMessage(C::colorize("&r&7&o    pouring forth the relentless Hollow Guardians"));
                        $player->sendMessage(C::colorize("&r&7&o     to bring chaos and destruction to the realm."));
                        $player->sendMessage(C::colorize("&r&f➥ &lSpawned at&r&f: &b" . $position->getFloorX() . "&r&f, &b" . $position->getFloorZ()));
                        break;
                    case "astral":
                        $player->sendMessage(C::colorize("          &r&l&f<&r&3Incursion&f: &dAstral&l&f>"));  
                        $player->sendMessage(C::colorize("&r&7&o A shimmering tear in reality reveals the"));
                        $player->sendMessage(C::colorize("&r&7&o  ethereal forms of the Astral Rangers,"));
                        $player->sendMessage(C::colorize("&r&7&o   guardians of the celestial planes,"));
                        $player->sendMessage(C::colorize("&r&7&o    descending upon the realm with celestial"));
                        $player->sendMessage(C::colorize("&r&7&o        fervor to defend their dominion."));
                        $player->sendMessage(C::colorize("&r&f➥ &lSpawned at&r&f: &b" . $position->getFloorX() . "&r&f, &b" . $position->getFloorZ()));
                        break;  
                }
            }

            self::spawnEndCrystal($position, $incursionType);
        });
    }

    public static function spawnIncursion(string $incursionType): void {
        self::getRandomPosition(function (Position $position) use ($incursionType) {
            $players = Server::getInstance()->getOnlinePlayers();
    
            foreach ($players as $player) {
                switch ($incursionType) {
                    case "soul":
                        $player->sendMessage(C::colorize("              &r&l&f<&r&fIncursion&f: &4Soul&l&f>"));
                        $player->sendMessage(C::colorize("&r&7&o A dimensional rift has torn open, unleashing the holy"));
                        $player->sendMessage(C::colorize("&r&7&o    forces of the Soul Guardians upon the realm."));
                        $player->sendMessage(C::colorize("&r&7&o        They have come to invade and conquer!"));
                        $player->sendMessage(C::colorize("&r&f➥ &lSpawned at&r&f: &b" . $position->getFloorX() . "&r&f, &b" . $position->getFloorZ()));
                        break;
                    case "hollow":
                        $player->sendMessage(C::colorize("              &r&l&f<&r&fIncursion&f: &fHollow&l&f>"));
                        $player->sendMessage(C::colorize("&r&7&o The fabric of reality shivers as a rift opens,"));
                        $player->sendMessage(C::colorize("&r&7&o    pouring forth the relentless Hollow Guardians"));
                        $player->sendMessage(C::colorize("&r&7&o     to bring chaos and destruction to the realm."));
                        $player->sendMessage(C::colorize("&r&f➥ &lSpawned at&r&f: &b" . $position->getFloorX() . "&r&f, &b" . $position->getFloorZ()));
                        break;
                    case "astral":
                        $player->sendMessage(C::colorize("              &r&l&f<&r&fIncursion&f: &dAstral&l&f>"));  
                        $player->sendMessage(C::colorize("&r&7&o A shimmering tear in reality reveals the"));
                        $player->sendMessage(C::colorize("&r&7&o  ethereal forms of the Astral Rangers,"));
                        $player->sendMessage(C::colorize("&r&7&o   guardians of the celestial planes,"));
                        $player->sendMessage(C::colorize("&r&7&o    descending upon the realm with celestial"));
                        $player->sendMessage(C::colorize("&r&7&o        fervor to defend their dominion."));
                        $player->sendMessage(C::colorize("&r&f➥ &lSpawned at&r&f: &b" . $position->getFloorX() . "&r&f, &b" . $position->getFloorZ()));
                        break;  
                }
            }
    
            self::spawnEndCrystal($position, $incursionType);
        });
    }    

    public static function getRandomPosition(callable $callback): void {
        $level = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $radius = 250;
        $maxDistance = 1000;
    
        $x = mt_rand(-$maxDistance, $maxDistance);
        $z = mt_rand(-$maxDistance, $maxDistance);
    
        if (sqrt($x * $x + $z * $z) <= $radius) {
            $x = ($x < 0 ? -1 : 1) * ($radius + mt_rand(0, $maxDistance - $radius));
            $z = ($z < 0 ? -1 : 1) * ($radius + mt_rand(0, $maxDistance - $radius));
        }
    
        $chunkX = $x >> 4;
        $chunkZ = $z >> 4;
    
        if ($level->isChunkLoaded($chunkX, $chunkZ)) {
            $y = $level->getHighestBlockAt($x, $z);
            $position = new Position($x, $y, $z, $level);
            $callback($position);
        } else {
            $level->orderChunkPopulation($chunkX, $chunkZ, null)->onCompletion(
                function (Chunk $chunk) use ($level, $x, $z, $callback) {
                    $y = $level->getHighestBlockAt($x, $z);
                    $position = new Position($x, $y, $z, $level);
                    $callback($position);
                },
                static function () {
                    Loader::getInstance()->getLogger()->warning("Failed to spawn incursion! Chunk not loaded.");
                }
            );
        }
    }

    public static function spawnEndCrystal(Position $position, string $incursionType): void {
        switch ($incursionType) {
            case "astral":
                $crystal = new AstralCrystal(new Location($position->getFloorX(), $position->getFloorY(), $position->getFloorZ(), $position->getWorld(), 50, 50));
                $crystal->spawnToAll();
            
                self::spawnMobs($position, $incursionType);
                break;
            case "hollow":
                $crystal = new HollowCrystal(new Location($position->getFloorX(), $position->getFloorY(), $position->getFloorZ(), $position->getWorld(), 50, 50));    
                $crystal->spawnToAll();
                self::spawnMobs($position, $incursionType);
                break;
            case "soul":
                $crystal = new SoulCrystal(new Location($position->getFloorX(), $position->getFloorY(), $position->getFloorZ(), $position->getWorld(), 50, 50));
                $crystal->spawnToAll();
                self::spawnMobs($position, $incursionType);
                break;
        }
    }

    public static function spawnMobs(Position $position, string $incursionType): void {
        $mobClass = null;
        $world = $position->getWorld();
    
        switch ($incursionType) {
            case "soul":
                $mobClass = SoulGuardian::class;
                break;
            case "hollow":
                $mobClass = HollowGuardian::class;
                break;
            case "astral":
                $mobClass = AstralRanger::class;
                break;
        }
    
        if ($mobClass !== null) {
            for ($i = 0; $i < 5; $i++) {
                $mobX = $position->getFloorX() + mt_rand(-5, 5);
                $mobZ = $position->getFloorZ() + mt_rand(-5, 5);

                $mobY = $world->getHighestBlockAt($mobX, $mobZ) + 1;

                $mobLocation = new Location($mobX, $mobY, $mobZ, $world, 0, 0);
                /** @var Entity $mob */
                $mob = new $mobClass($mobLocation);
                $mob->spawnToAll();
            }
        }
    }
    
}
