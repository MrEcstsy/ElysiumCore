<?php

namespace xtcy\ElysiumCore\listeners;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\Cobweb;
use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\item\Sword;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
use pocketmine\world\sound\XpLevelUpSound;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\enchants\all\ReforgedEnchantment;
use xtcy\ElysiumCore\enchants\armor\AngelicEnchantment;
use xtcy\ElysiumCore\enchants\armor\ArmoredEnchantment;
use xtcy\ElysiumCore\enchants\armor\boots\GearsEnchantment;
use xtcy\ElysiumCore\enchants\armor\chestplate\MirroredEnchantment;
use xtcy\ElysiumCore\enchants\armor\CurseEnchantment;
use xtcy\ElysiumCore\enchants\armor\DiminishEnchantment;
use xtcy\ElysiumCore\enchants\armor\FrostbiteEnchantment;
use xtcy\ElysiumCore\utils\AllyChecks;
use xtcy\ElysiumCore\enchants\armor\helmet\AquaticEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\ClarityEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\DrunkEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\EndershiftEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\GlowingEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\HeavyEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\TrappedEnchantment;
use xtcy\ElysiumCore\enchants\armor\helmet\WellfedEnchantment;
use xtcy\ElysiumCore\enchants\armor\OverloadEnchantment;
use xtcy\ElysiumCore\enchants\armor\PoisonedEnchantment;
use xtcy\ElysiumCore\enchants\armor\TankEnchantment;
use xtcy\ElysiumCore\enchants\armor\ValorEnchantment;
use xtcy\ElysiumCore\enchants\chestplate\BlazedEnchantment;
use xtcy\ElysiumCore\enchants\armor\leggings\JellyEnchantment;
use xtcy\ElysiumCore\enchants\armor\leggings\RedeemerEnchantment;
use xtcy\ElysiumCore\enchants\tools\AutoSmeltEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\CleaveEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\ConfusionEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\DemonicFinisherEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\FearEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\HolyEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\PummelEnchantment;
use xtcy\ElysiumCore\enchants\tools\ExperienceEnchantment;
use xtcy\ElysiumCore\enchants\tools\HasteEnchantment;
use xtcy\ElysiumCore\enchants\tools\LuckyEnchantment;
use xtcy\ElysiumCore\enchants\tools\ObsidianDestroyerEnchantment;
use xtcy\ElysiumCore\enchants\tools\SatansTreatEnchantment;
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
use xtcy\ElysiumCore\entities\FloatingTextEntity;
use xtcy\ElysiumCore\items\Items;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\tasks\AngelicHealingTask;
use xtcy\ElysiumCore\utils\EnchantCooldownTrait;
use xtcy\ElysiumCore\utils\EnchantUtils;
use Wertzui123\CBHeads\Main;
use xtcy\ElysiumCore\enchants\armor\boots\AscendedEnchantment;
use xtcy\ElysiumCore\enchants\tools\axe\BleedEnchantment;
use xtcy\ElysiumCore\enchants\util\CustomEnchantment;

class EnchantListener implements Listener
{
    use EnchantCooldownTrait;

    /** @var array */
    public static array $activeAngelicTasks = [];

    private array $rageHits = [];
    
    private array $rageActivated = [];

    private array $damageBuffs = [];

    private array $silenced = [];

    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $enchantments = $item->getEnchantments();
        $block = $event->getBlock();

        foreach ($enchantments as $enchantmentInstance) {
            $enchantment = $enchantmentInstance->getType();

            if ($enchantment instanceof AutoSmeltEnchantment) {
                $newDrops = $event->getDrops();
                foreach ($newDrops as $k => $drop) {
                    $item = match ($drop->getTypeId()) {
                        ItemTypeIds::RAW_COPPER => VanillaItems::COPPER_INGOT(),
                        ItemTypeIds::RAW_IRON => VanillaItems::IRON_INGOT(),
                        ItemTypeIds::RAW_GOLD => VanillaItems::GOLD_INGOT(),
                        BlockTypeIds::COBBLESTONE => VanillaBlocks::COBBLESTONE()->asItem(),
                        default => null
                    };
                    if ($item !== null) {
                        $newDrops[$k] = $item;
                    }
                }

                $event->setDrops($newDrops);
            } elseif ($enchantment instanceof LuckyEnchantment) {
                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                    $player->getXpManager()->addXp($enchantmentInstance->getLevel() * 5);
                }
            } elseif ($enchantment instanceof SatansTreatEnchantment) {
                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 2) {
                    $keyTypes = ["cipher", "zenith", "empyrean"];
                    $keyType = $keyTypes[array_rand($keyTypes)];
                    $key = Items::getCrateKey($keyType);
                    $player->getWorld()->dropItem($player->getPosition()->asVector3(), $key);
                }
            } elseif ($enchantment instanceof ReforgedEnchantment) {
                if (count($enchantments) > 1) {
                    $enchKeys = array_keys($enchantments);
                    $randKey = $enchKeys[array_rand($enchKeys)];
                    $randEnchantment = $enchantments[$randKey];

                    if ($randEnchantment->getType() instanceof CustomEnchantment) {
                        if ($item instanceof Durable) {
                            if ($item->getDamage() >= $item->getMaxDurability() - 1) {
                                if (mt_rand(1, 100) < $randEnchantment->getLevel() * 100) {
                                    $item->removeEnchantment($randEnchantment->getType());
                                    $item->setDamage(0);
                                    $player->getInventory()->setItemInHand($item);
                                    $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();

        $enchantments = $item->getEnchantments();
        foreach ($enchantments as $enchantmentInstance) {
            $enchantment = $enchantmentInstance->getType();

            if ($enchantment instanceof ObsidianDestroyerEnchantment) {
                if ($block->getTypeId() === BlockTypeIds::OBSIDIAN) {
                    if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                        $world = $block->getPosition()->getWorld();
                        $position = $block->getPosition();
                        $world->setBlock($position, VanillaBlocks::AIR());
                        $obsidianItem = VanillaBlocks::OBSIDIAN()->asItem();
                        $world->dropItem($position, $obsidianItem);
                        $event->cancel();
                        return;
                    }
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        $cause = $event->getCause();

        if ($entity instanceof Player) {
            $armor = $entity->getArmorInventory();
            foreach ($armor->getContents() as $item) {
                $enchantments = $item->getEnchantments();
                foreach ($enchantments as $enchinstance) {
                    $ench = $enchinstance->getType();

                    if ($ench instanceof JellyEnchantment) {
                        if ($cause === EntityDamageEvent::CAUSE_FALL) {
                            $level = $enchinstance->getLevel();
                            $chance = 0;
                            switch ($level) {
                                case 1:
                                    $chance = 40;
                                    break;
                                case 2:
                                    $chance = 80;
                                    break;
                                case 3:
                                    $chance = 100;
                                    break;
                            }
                            
                            if (mt_rand(1, 100) <= $chance) {
                                $event->cancel();
                                $entity->sendMessage(C::colorize("&r&l&b** &r&bNegated Fall Damage &l**"));
                                return;
                            }
                        }
                    }
                }
            }
        }
    }

    private function applyExtraDamage(int $rageHits): float {
        return 1.0 + ($rageHits * 0.5);
    }

    public function onEntityDamageTwo(EntityDamageByEntityEvent $event): void {
        $player = $event->getDamager();
        $victim = $event->getEntity();

        if ($player instanceof Player && $victim instanceof Player) {
            $armor = $player->getArmorInventory();
            $victimArmor = $victim->getArmorInventory();

            foreach ($armor->getContents() as $item) {
                $enchantments = $item->getEnchantments();
                foreach ($enchantments as $enchinstance) {
                    $ench = $enchinstance->getType();
                    if ($ench instanceof TrappedEnchantment) {
                        if (mt_rand(1, 100) < $enchinstance->getLevel() * 2) {
                            $victimLocation = $victim->getLocation();
                
                            $trapDurationSeconds = 5;
                            $trapDurationTicks = $trapDurationSeconds * 20;
                
                            $world = $victimLocation->getWorld();
                            $positions = [
                                $victimLocation,
                                $victimLocation->add(1, 0, 0),
                                $victimLocation->add(-1, 0, 0),
                                $victimLocation->add(0, 0, 1),
                                $victimLocation->add(0, 0, -1),
                                $victimLocation->add(1, 0, 1),
                                $victimLocation->add(1, 0, -1),
                                $victimLocation->add(-1, 0, 1),
                                   $victimLocation->add(-1, 0, -1)
                            ];
                            $originalBlocks = [];
                            foreach ($positions as $pos) {
                                $block = $world->getBlock($pos);
                                $originalBlocks[] = [$pos, $block];
                                $world->setBlock($pos, VanillaBlocks::COBWEB());
                            }
                
                            $slownessLevel = 4;
                            $victim->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), $trapDurationTicks, $slownessLevel));

                            Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player, $originalBlocks, $world): void {
                                foreach ($originalBlocks as [$pos, $originalBlock]) {
                                    $block = $world->getBlock($pos);
                                    if ($block instanceof Cobweb) {
                                        $world->setBlock($pos, $originalBlock);
                                    }
                                }
                            }), $trapDurationTicks);
                        }
                    } 
                }
            }

            foreach ($victimArmor->getContents() as $item) {
                $enchantments = $item->getEnchantments();
                foreach ($enchantments as $enchinstance) {
                    $ench = $enchinstance->getType();
                    if ($ench instanceof AscendedEnchantment) {
                        if (mt_rand(1, 100) < $enchinstance->getLevel() * 40) {
                            if ($victim->getHealth() <= 8) {
                                $victim->setMotion($victim->getMotion()->add(0, 1000, 0));
                            }
                        }
                    } elseif ($ench instanceof ReforgedEnchantment) {
                        if (count($enchantments) > 1) {
                            $enchKeys = array_keys($enchantments);
                            $randKey = $enchKeys[array_rand($enchKeys)];
                            $randEnchantment = $enchantments[$randKey];
        
                            if ($randEnchantment->getType() instanceof CustomEnchantment) {
                                if ($item instanceof Durable) {
                                    if ($item->getDamage() >= $item->getMaxDurability() - 1) {
                                        if (mt_rand(1, 100) < $randEnchantment->getLevel() * 100) {
                                            $item->removeEnchantment($randEnchantment->getType());
                                            $item->setDamage(0);
                                            $player->getInventory()->setItemInHand($item);
                                            $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function onEntityDamageThree(EntityDamageByEntityEvent $event): void {
         $player = $event->getDamager();
         $victim = $event->getEntity();

         if ($player instanceof Player && $victim instanceof Player) {
            $item = $player->getInventory()->getItemInHand();
            $enchantments = $item->getEnchantments();
            $playerName = $player->getName();
            $playerSession = Loader::getPlayerManager()->getSession($player);
            $victimSession = Loader::getPlayerManager()->getSession($victim);

            if (!isset($this->rageActivated[$playerName])) {
                $this->rageActivated[$playerName] = false;
            }
            if (!isset($this->rageHits[$playerName])) {
                $this->rageHits[$playerName] = 0;
            }

            $victimName = $victim->getName();
            if (isset($this->rageActivated[$victimName])) {
                $this->rageActivated[$victimName] = false;
                $this->rageHits[$victimName] = 0;
            }

            foreach ($enchantments as $enchinstance) {
                $ench = $enchinstance->getType();
                if ($ench instanceof RageEnchantment) {
                    $level = $enchinstance->getLevel();
                    $chance = 0;
                    $cooldown = 10;

                    switch ($level) {
                        case 1:
                            $chance = 25;
                            break;
                        case 2:
                            $chance = 30;
                            break;
                        case 3:
                            $chance = 35;
                            break;
                        case 4:
                            $chance = 40;
                            break;
                        case 5:
                            $chance = 45;
                            break;
                        case 6:
                            $chance = 50;
                            break;    
                    }

                    if ($this->rageActivated[$playerName] || mt_rand(1, 100) <= $chance) {
                        if (!$this->rageActivated[$playerName]) {
                            $this->rageActivated[$playerName] = true;
                            $this->rageHits[$playerName] = 1;
                        } else {
                            $this->rageHits[$playerName]++;
                        }

                        $this->rageHits[$playerName] = min($this->rageHits[$playerName], 5);

                        $extraDamage = $this->rageHits[$playerName];

                        $event->setBaseDamage($event->getBaseDamage() + $extraDamage);

                        if (mt_rand(1, 100) <= $chance) {
                            $extraDamage += 1;
                        }

                        $victimLocation = $victim->getLocation();
                        $offsetX = mt_rand(-1, 1) * 0.5;
                        $offsetY = mt_rand(1, 2) * 0.5; 
                        $offsetZ = mt_rand(-1, 1) * 0.5;
                
                        $newLocation = new Location(
                            $victimLocation->getX() + $offsetX,
                            $victimLocation->getY() + $offsetY,
                            $victimLocation->getZ() + $offsetZ,
                            $victimLocation->getWorld(),
                            $victimLocation->getYaw(),
                            $victimLocation->getPitch()       
                        );      

                        $floatingText = new FloatingTextEntity($newLocation);
                        $floatingText->setText("§r§l§6Rage §r§7x" . $this->rageHits[$playerName]);
                        $floatingText->spawnToAll();

                        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($floatingText): void {
                            $floatingText->flagForDespawn();
                        }), 20);
                    } 
                } elseif ($ench instanceof BleedEnchantment) {
                    $level = $enchinstance->getLevel();
                    $chance = 0;
                    $dmg = mt_rand(1, 3);
                    $slowlvl = 0;

                    switch ($level) {
                        case 1:
                            $chance = 8;
                            break;
                        case 2:
                            $chance = 15;
                            break;
                        case 3:
                            $chance = 23;
                            $slowlvl = 1;
                            break;
                        case 4:
                            $chance = 30;
                            $slowlvl = 1;
                            break;
                        case 5:
                            $chance = 44;
                            $slowlvl = 1;  
                            break;
                        case 6:
                            $chance = 60;  
                            $slowlvl = 2;
                            break;
                    }

                    if (mt_rand(1, 100) <= $chance) {
                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), Utils::secondsToTicks(100), $slowlvl));
                        $victim->sendMessage(C::colorize("&r&4You're bleeding!"));

                        $victim->setHealth($victim->getHealth() - $dmg);

                        $this->applyBleedEffect($victim, $dmg, 4);
                    }
                } elseif ($ench instanceof ReforgedEnchantment) {
                    if (count($enchantments) > 1) {
                        $enchKeys = array_keys($enchantments);
                        $randKey = $enchKeys[array_rand($enchKeys)];
                        $randEnchantment = $enchantments[$randKey];
    
                        if ($randEnchantment->getType() instanceof CustomEnchantment) {
                            if ($item instanceof Durable) {
                                if ($item->getDamage() >= $item->getMaxDurability() - 1) {
                                    if (mt_rand(1, 100) < $randEnchantment->getLevel() * 100) {
                                        $item->removeEnchantment($randEnchantment->getType());
                                        $item->setDamage(0);
                                        $player->getInventory()->setItemInHand($item);
                                        $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100));
                                    }
                                }
                            }
                        }
                    }
                } elseif ($ench instanceof DoubleStrikeEnchantment) {
                    $level = $enchinstance->getLevel();
                    $cooldown = 15;
                    $chance = 0;

                    switch ($level) {
                        case 1:
                            $chance = 8;
                            break;
                        case 2:
                            $chance = 13;
                            break;
                        case 3:
                            $chance = 19;
                            break;
                    }

                    if (mt_rand(1, 100) <= $chance) {
                        if ($playerSession->getCooldown("doubleStrike") === 0 | $playerSession->getCooldown("doubleStrike") === null) {
                            $damage = $event->getFinalDamage();
                            $victim->attack(new EntityDamageEvent($victim, EntityDamageEvent::CAUSE_MAGIC, $damage));

                            $player->sendMessage(C::colorize("&r&l&6** &r&6DOUBLE STRIKE &r&6&l**"));
                            $playerSession->addCooldown("doubleStrike", $cooldown);
                        }
                    }
                } elseif ($ench instanceof SilenceEnchantment) {
                    $level = $enchinstance->getLevel();
                    $chance = 0;
                    $time = 0;
                    $cooldown = 5;

                    switch ($level) {
                        case 1:
                            $chance = 1.8;
                            $time = 3;
                            break;
                        case 2:
                            $chance = 2.5;
                            $time = 3;
                            break;
                        case 3:
                            $chance = 2.9;
                            $time = 5;
                            break;
                        case 4:
                            $chance = 3.1;
                            $time = 7;
                            break;
                    }

                    if (mt_rand(1, 100) <= $chance) {
                        if ($playerSession->getCooldown("silence") === 0 | $playerSession->getCooldown("silence") === null) {
                            $victimName = $victim->getName();
                    
                            $this->silenced[$victimName] = time() + $time;
    
                            $victimLocation = $victim->getLocation();
                            $offsetX = mt_rand(-1, 1) * 0.5;
                            $offsetY = mt_rand(1, 2) * 0.5; 
                            $offsetZ = mt_rand(-1, 1) * 0.5;

                            $newLocation = new Location(
                                $victimLocation->getX() + $offsetX,
                                $victimLocation->getY() + $offsetY,
                                $victimLocation->getZ() + $offsetZ,
                                $victimLocation->getWorld(),
                                $victimLocation->getYaw(),
                                $victimLocation->getPitch()
                            );

                            $floatingText = new FloatingTextEntity($newLocation);
                            $floatingText->setText(C::colorize("&r&5&l* SILENCED &r&7[{$time}] &5&l*"));
                            $floatingText->spawnToAll();

                            Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($floatingText): void {
                                $floatingText->flagForDespawn();
                            }), 20);
                            
                            $playerSession->addCooldown("silence", $cooldown);
                        }
                    }
                }
            }
        }
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getDamager();
        $victim = $event->getEntity();

        if ($player instanceof Player && $victim instanceof Player) {
            $item = $player->getInventory()->getItemInHand();
            $armor = $player->getArmorInventory()->getContents();
            $enchantments = $item->getEnchantments();
            $victimArmorInventory = $victim->getArmorInventory();
            $playerName = $player->getName();
            $playerSession = Loader::getPlayerManager()->getSession($player);
            $victimSession = Loader::getPlayerManager()->getSession($victim);

            foreach ($armor as $armorPiece) {
                $armorEnchants = $armorPiece->getEnchantments();
                foreach ($enchantments as $enchantmentInstance) {
                    foreach ($armorEnchants as $armorEnchant) {
                        $enchantment = $enchantmentInstance->getType();
                        if ($enchantment instanceof ExecuteEnchantment) {
                                if ($enchantmentInstance->getLevel() > 0) {
                                    $level = $enchantmentInstance->getLevel();
                                    $cooldown = 2;
                                    $dmgincrement = mt_rand(10, 50);

                                    switch ($level) {
                                        case 1:
                                            $chance = 9;
                                            break;
                                        case 2:
                                            $chance = 14;
                                            break;
                                        case 3:
                                            $chance = 19;
                                            break;
                                        case 4:
                                            $chance = 24;
                                            break;
                                        case 5:
                                            $chance = 29;
                                            break;
                                        case 6:
                                            $chance = 32;
                                            break;
                                        case 7:
                                            $chance = 40;
                                            break;

                                    }

                                    if (mt_rand(1, 100) <= $chance) {
                                        if ($victim->getHealth() < 6) {
                                            $victim->setHealth($player->getHealth() - $dmgincrement);
                                            $player->sendMessage(C::colorize("&r&b&l** &r&bExecute &l**"));
                                            $this->setCooldown($player, $cooldown);
                                        }
                                    }
                                }
                            } elseif ($enchantment instanceof FearEnchantment) {
                                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                    $victim->getEffects()->add(new EffectInstance(VanillaEffects::WEAKNESS(), 20 * 3, $enchantmentInstance->getLevel(), false));
                                }
                            } elseif ($enchantment instanceof SlownessEnchantment) {
                                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                    if (!$victim->getEffects()->has(VanillaEffects::SLOWNESS())) {
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), Utils::secondsToTicks(5), 4));
                                        $this->setCooldown($player, 60);
                                    }
                                }
                            } elseif ($enchantment instanceof FrostbiteEnchantment) {
                                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                    if (!$victim->getEffects()->has(VanillaEffects::SLOWNESS())) {
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), Utils::secondsToTicks(5), 4));
                                        $this->setCooldown($player, 60);
                                    }
                                }
                            } elseif ($enchantment instanceof ConfusionEnchantment) {
                                $level = $enchantmentInstance->getLevel();
                                $chance = 0;
                                $cooldown = 0;
                                $duration = 0;

                                switch ($level) {
                                    case 1:
                                        $chance = 13;
                                        $cooldown = 4;
                                        $duration = 40;
                                        break;
                                    case 2:
                                        $chance = 19;
                                        $cooldown = 5;
                                        $duration = 80;
                                        break;
                                    case 3:
                                        $chance = 24;
                                        $cooldown = 6;
                                        $duration = 120;
                                        break;
                                }

                                if (mt_rand(1, 100) <= $chance) {
                                    if ($playerSession->getCooldown("confusion") === 0 || $playerSession->getCooldown("confusion") === null) {
                                        $playerSession->addCooldown("confusion", $cooldown);
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), Utils::secondsToTicks($duration), 0));
                                    }
                                }
                            } elseif ($enchantment instanceof InsomniaEnchantment) {
                                $level = $enchantmentInstance->getLevel();
                                $chance = 0;
                                $cooldown = 0;
                                $slowdura = 0;
                                $fatiguedura = 0;
                                $nauseadura = 0;

                                switch ($level) {
                                    case 1:
                                        $chance = 10;
                                        $cooldown = 10;
                                        $slowdura = 40;
                                        $fatiguedura = 60;
                                        $nauseadura = 40;
                                        break;
                                    case 2:
                                        $chance = 12;
                                        $cooldown = 10;
                                        $slowdura = 80;
                                        $fatiguedura = 100;
                                        $nauseadura = 80;
                                        break;
                                    case 3:
                                        $chance = 14;
                                        $cooldown = 10;
                                        $slowdura = 100;
                                        $fatiguedura = 120;
                                        $nauseadura = 100;
                                    case 4:
                                        $chance = 16;
                                        $cooldown = 9;
                                        $slowdura = 120;
                                        $fatiguedura = 140;
                                        $nauseadura = 120;
                                        break;
                                    case 5:
                                        $chance = 18;
                                        $cooldown = 9;
                                        $slowdura = 140;
                                        $fatiguedura = 160;
                                        $nauseadura = 140;
                                        break;
                                    case 6:
                                        $chance = 20;
                                        $cooldown = 8;
                                        $slowdura = 160;
                                        $fatiguedura = 180;
                                        $nauseadura = 160;
                                        break;
                                    case 7:
                                        $chance = 22;
                                        $cooldown = 8;
                                        $slowdura = 180;
                                        $fatiguedura = 200;
                                        $nauseadura = 180;
                                        break;
                                }

                                if (mt_rand(1, 100) <= $chance) {
                                    if ($playerSession->getCooldown("insomnia") === 0 || $playerSession->getCooldown("insomnia") === null) {
                                        $playerSession->addCooldown("insomnia", $cooldown);
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), Utils::secondsToTicks($slowdura), 0));
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::MINING_FATIGUE(), Utils::secondsToTicks($fatiguedura), 0));
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), Utils::secondsToTicks($nauseadura), 0));
                                    }
                                }
                            } elseif ($enchantment instanceof FeatherweightEnchantment) {
                                $level = $enchantmentInstance->getLevel();
                                $chance = 0;
                                $cooldown = 2;
                                $duration = 0;
                                $level = 0;

                                switch ($level) {
                                    case 1:
                                        $chance = 35;
                                        $duration = 60;
                                        break;
                                    case 2:
                                        $chance = 55;
                                        $duration = 80;
                                        $level = 1;
                                        break;
                                    case 3:
                                        $chance = 75;
                                        $duration = 100;
                                        $level = 2;
                                        break;
                                }

                                if (mt_rand(1, 100) <= $chance) {
                                    if ($playerSession->getCooldown("featherweight") === 0 || $playerSession->getCooldown("featherweight") === null) {
                                        $player->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), Utils::secondsToTicks($duration), $level));
                                        $this->setCooldown($player, $cooldown);
                                    }
                                }
                            } elseif ($enchantment instanceof PummelEnchantment) {
                                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                    if (!AllyChecks::isAlly($victim, $player)) {
                                        $entities = $player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy(5, 5, 5), $player);

                                        foreach ($entities as $entity) {
                                            if ($entity instanceof Living) {
                                                if (!AllyChecks::isAlly($entity, $player)) {
                                                    $effect = new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 3, $enchantmentInstance->getLevel() - 1);
                                                    $entity->getEffects()->add($effect);
                                                }
                                            }
                                        }
                                    }
                                } 
                            } elseif ($enchantment instanceof VampireEnchantment) {
                                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                    
                                    $scheduler = Loader::getInstance()->getScheduler();
                                    $scheduler->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                                        $currentHealth = $player->getHealth();
                                        $newHealth = min($currentHealth + 3, $player->getMaxHealth());
                                        $player->setHealth($newHealth);
                                    }), 20 * 3); 
                                }
                            } elseif ($enchantment instanceof DiminishEnchantment) {
                                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                    if (!$this->isSilenced($player)) {
                                        foreach ($victimArmorInventory->getContents() as $armorItem) {
                                            if ($armorItem instanceof Durable) {
                                                $currentDamage = $armorItem->getDamage();
                                                $maxDamage = $armorItem->getMaxDurability();
                                
                                                $newDamage = $currentDamage + $enchantmentInstance->getLevel();
                                                if ($newDamage > $maxDamage) {
                                                    $newDamage = $maxDamage;
                                                }
                                
                                                $armorItem->setDamage($newDamage);
                                            }
                                        }
                                    }
                                }
                            } elseif ($enchantment instanceof CleaveEnchantment) {
                                $level = $enchantmentInstance->getLevel();
                                $chance = 0;
                                $radius = 0;
                                $damage = mt_rand(1, 3);
                                $cooldown = 0;

                                $entities = $player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius), $player);

                                switch ($level) {
                                    case 1:
                                        $chance = 4;
                                        $cooldown = 8;
                                        $radius = 1;
                                        break;
                                    case 2:
                                        $chance = 5;
                                        $cooldown = 8;
                                        $radius = 2;
                                        break;
                                    case 3:
                                        $chance = 6;
                                        $cooldown = 9;
                                        $radius = 3;
                                        break;
                                    case 4:
                                        $chance = 6;
                                        $cooldown = 9;    
                                        $radius = 4;
                                        break;
                                    case 5:
                                        $chance = 9;
                                        $cooldown = 10;
                                        $radius = 5;
                                        break;
                                    case 6:
                                        $chance = 12;
                                        $cooldown = 12;
                                        $radius = 6;
                                        break;
                                    case 7:
                                        $chance = 15;
                                        $cooldown = 14;
                                        $radius = 7;
                                }

                                if (mt_rand(1, 100) <= $chance) {
                                    foreach ($entities as $entity) {
                                        if ($entity instanceof Player && $entity !== $victim) {
                                            $damage = $event->getBaseDamage() + $damage;
                                            $newEvent = new EntityDamageByEntityEvent($player, $entity, $event->getCause(), $damage);
                                            $entity->attack($newEvent);
                                            $entity->sendMessage(C::colorize("&r&e&l** &r&eCLEAVE &7({$player->getName()}) &r&e&l**"));
                                        }
                                    }
                                }

                            } elseif ($enchantment instanceof HolyEnchantment) {
                                $level = $enchantmentInstance->getLevel();
                                $chance = 0;
                                $cooldown = 8;

                                switch ($level) {
                                    case 1:
                                        $chance = 12;
                                        break;
                                    case 2:
                                        $chance = 16;
                                        break;  
                                    case 3:
                                        $chance = 22;
                                        break;
                                    case 4:
                                        $chance = 36;
                                        break;
                                    }

                                if (mt_rand(1, 100) <= $chance) {
                                    Utils::bless($player);
                                }
                            } elseif ($enchantment instanceof VampiricDevourEnchantment) {
                                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 2) {
                                    if ($player->getHealth() >= $player->getMaxHealth()) {
                                        return; 
                                    }
                            
                                    $healthToRestore = $player->getMaxHealth() - $player->getHealth();
                                    $player->heal(new EntityRegainHealthEvent($player, $healthToRestore, EntityRegainHealthEvent::CAUSE_MAGIC));
                                    Utils::playSound($player, "mob.enderdragon.flap");
                                }
                            } elseif ($enchantment instanceof DemonicFinisherEnchantment) {
                                if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                    $victimHealth = $victim->getHealth();
                    
                                    if ($victimHealth <= 8) {
                                        $maxDamage = 10; 
                                        $damageDealt = mt_rand(1, $maxDamage);
                            
                                        $event->setBaseDamage($event->getBaseDamage() + $damageDealt);
                                    }
                                }
                            } 
                        }
                    } 
                }

                foreach ($victimArmorInventory->getContents() as $item) {
                    $enchantments = $item->getEnchantments();
                    foreach ($enchantments as $enchantmentInstance) {
                        $enchantment = $enchantmentInstance->getType();
                        if ($enchantment instanceof EndershiftEnchantment) {
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;
                            $cooldown = 0;
                            $speeddura = 0;
                            $speedlvl = 0;
                            $absorptiondura = 0;
                            $absorptionlvl = 0;

                            switch ($level) {
                                case 1:
                                    $chance = 16;
                                    $cooldown = 6;
                                    $speeddura = 120;
                                    $speedlvl = 1;
                                    $absorptiondura = 80;
                                    break;
                                case 2:
                                    $chance = 17;
                                    $cooldown = 8;
                                    $speeddura = 120;
                                    $speedlvl = 1;
                                    $absorptiondura = 80;   
                                    $absorptionlvl = 1;
                                    break;
                                case 3:
                                    $chance = 18;
                                    $cooldown = 12;
                                    $speeddura = 120;
                                    $speedlvl = 1;
                                    $absorptiondura = 80;
                                    $absorptionlvl = 2;
                                    break;     
                            }

                            if (mt_rand(1, 100) <= $chance) {
                                if (!$this->isSilenced($victim)) {
                                    if ($victim->getHealth() < 6) {
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), Utils::secondsToTicks($speeddura), $speedlvl));
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::ABSORPTION(), Utils::secondsToTicks($absorptiondura), $absorptionlvl));
                                        $victim->sendMessage(C::colorize("&r&dYou were about to die, so you have entered the Ender Dimension!"));
                                    }
                                }
                            }
                        } elseif ($enchantment instanceof AngelicEnchantment) {
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;
                            $cooldown = 0;
                            $increment = mt_rand(1, 3);

                            switch ($level) {
                                case 1:
                                    $chance = 9;
                                    $cooldown = 7;
                                    break;
                                case 2:
                                    $chance = 14;
                                    $cooldown = 9;
                                    break;
                                case 3:
                                    $chance = 17;
                                    $cooldown = 11;
                                    break;
                                case 4:
                                    $chance = 26;
                                    $cooldown = 13;
                                    $increment = mt_rand(1, 4);    
                                    break;
                                case 5:
                                    $chance = 34;
                                    $cooldown = 15;
                                    $increment = mt_rand(1, 4);
                                    break;    
                            }

                            if (mt_rand(1, 100) <= $chance) {
                                if ($victimSession->getCooldown("angelic") === 0 || $victimSession->getCooldown("angelic") === null) {
                                    $victim->setHealth($victim->getHealth() + $increment);
                                    $victimSession->addCooldown("angelic", $cooldown);
                                    $victim->sendMessage(C::colorize("&r&e&l** &r&eANGELIC &r&7(&c+ $increment&r&7) &r&e&l**"));
                                }
                            }
                        } elseif ($enchantment instanceof RedeemerEnchantment) {
                            if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 2) {
                                $buffMultiplier = mt_rand(2, 3); 
                                $buffDurationSeconds = mt_rand(1, 3); 
                                $buffDurationTicks = $buffDurationSeconds * 20;
                        
                                $victimName = $victim->getName();
                                
                                $originalDamage = $event->getBaseDamage();
                                $newDamage = $originalDamage * $buffMultiplier;
                                $event->setBaseDamage($newDamage);
                                $this->damageBuffs[$victimName] = [
                                    'originalDamage' => $originalDamage,
                                    'expiryTime' => time() + $buffDurationSeconds
                                ];                        
                                Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($victimName): void {
                                    if (isset($this->damageBuffs[$victimName]) && time() >= $this->damageBuffs[$victimName]['expiryTime']) {
                                        unset($this->damageBuffs[$victimName]); 
                                    }
                                }), $buffDurationTicks);
                        
                            }
                        } elseif ($enchantment instanceof ArmoredEnchantment) {
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;
                            $cooldown = 8;
                            $dmgdecrease = 0;

                            switch ($level) {
                                case 1:
                                    $chance = 6;
                                    $dmgdecrease = 2;
                                    break;
                                case 2:
                                    $chance = 12;
                                    $dmgdecrease = 4;
                                    break;
                                case 3:
                                    $chance = 18;
                                    $dmgdecrease = 6;
                                    break;
                                case 4:
                                    $chance = 20;
                                    $dmgdecrease = 8;
                                    break;    
                            }

                            if (mt_rand(1, 100) <= $chance) {
                                if (!$this->isSilenced($victim)) {
                                    if ($victimSession->getCooldown("armored") === 0 || $victimSession->getCooldown("armored") === null) {
                                        $heldItem = $player->getInventory()->getItemInHand();
                                        if ($heldItem instanceof Sword) {
                                            $event->setBaseDamage($event->getBaseDamage() * (1 - ($dmgdecrease / 100)));
                                            $victimSession->addCooldown("armored", $cooldown);
                                        }
                                    }
                                }
                            }
                        } elseif ($enchantment instanceof BlazedEnchantment) {
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;
                            $cooldown = 2;
                            $duration = 0;

                            switch ($level) {
                                case 1:
                                    $chance = 21;
                                    $duration = 2;
                                    break;
                                case 2:
                                    $chance = 32;
                                    $duration = 4;
                                    break;
                                case 3:
                                    $chance = 49;
                                    $duration = 6;
                                    break;
                                case 4:
                                    $chance = 63;
                                    $duration = 8;
                                    break;
                            }

                            if (mt_rand(1, 100) <= $chance) {
                                if ($victimSession->getCooldown("blazed") === 0 || $victimSession->getCooldown("blazed") === null) {
                                    $player->setOnFire($duration);
                                    $victimSession->addCooldown("blazed", $cooldown);
                                }
                            }
                        } elseif ($enchantment instanceof CurseEnchantment) {
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;
                            $cooldown = 0;
                            $fatiguedura = 0;
                            $strengthdura = 0;
                            $resistancedura = 0;
                            $fatiguelvl = 0;
                            $strengthlvl = 0;
                            $resistancelvl = 0;
                            
                            switch ($level) {
                                case 1:
                                    $chance = 10;
                                    $cooldown = 7;
                                    $fatiguedura = 100;
                                    $strengthdura = 40;
                                    $resistancedura = 40;
                                    break;
                                case 2:
                                    $chance = 12;
                                    $cooldown = 7;
                                    $fatiguedura = 120;
                                    $strengthdura = 60;
                                    $resistancedura = 60;
                                    break;
                                case 3:
                                    $chance = 14;
                                    $cooldown = 7;
                                    $fatiguedura = 80;
                                    $fatiguelvl = 1;
                                    $strengthdura = 80;
                                    $resistancedura = 80;
                                    break;
                                case 4:
                                    $chance = 16;
                                    $cooldown = 7;
                                    $fatiguedura = 100;
                                    $fatiguelvl = 1;
                                    $strengthdura = 60;
                                    $strengthlvl = 1;
                                    $resistancedura = 60;
                                    $resistancelvl = 1;
                                    break;
                                case 5:
                                    $chance = 18;
                                    $cooldown = 7;
                                    $fatiguedura = 100;
                                    $fatiguelvl = 2;
                                    $strengthdura = 80;
                                    $strengthlvl = 1;
                                    $resistancedura = 80;
                                    $resistancelvl = 1;
                                    break;        

                            }

                            if (mt_rand(1, 100) <= $chance) {
                                if ($victimSession->getCooldown("curse") === 0 || $victimSession->getCooldown("curse") === null) {
                                    if ($victim->getHealth() > 10) {
                                        $player->getEffects()->add(new EffectInstance(VanillaEffects::MINING_FATIGUE(), $fatiguedura, $fatiguelvl));
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), $strengthdura, $strengthlvl));
                                        $victim->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), $resistancedura, $resistancelvl));
                                        $victimSession->addCooldown("curse", $cooldown);
                                    }
                                }
                            }
                        } elseif ($enchantment instanceof FrostbiteEnchantment) {
                            if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                $player->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 3, $enchantmentInstance->getLevel() - 1));
                            }
                        } elseif ($enchantment instanceof TankEnchantment) {
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;
                            $cooldown = 3;
                            $dmgdecrease = 0;

                            switch ($level) {
                                case 1:
                                    $chance = 5;
                                    $dmgdecrease = 2;
                                    break;
                                case 2:
                                    $chance = 8;
                                    $dmgdecrease = 4;
                                    break;
                                case 3:
                                    $chance = 12;
                                    $dmgdecrease = 6;
                                    break;
                                case 4:
                                    $chance = 16;
                                    $dmgdecrease = 8;
                                    break;
                            }

                            if (mt_rand(1, 100) <= $chance) {
                                if (!$this->isSilenced($victim)) {
                                    $heldItem = $player->getInventory()->getItemInHand();
                                    if ($heldItem instanceof Axe) {
                                        $event->setBaseDamage($event->getBaseDamage() * (1 - ($dmgdecrease / 100)));
                                        
                                    }
                                }
                            }

                        } elseif ($enchantment instanceof ValorEnchantment) {
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;
                            $cooldown = 4;
                            $dmgdecrease = mt_rand(1, 4);

                            switch ($level) {
                                case 1:
                                    $chance = 4;
                                    break;
                                case 2:
                                    $chance = 9;
                                    $dmgdecrease = mt_rand(8, 12);
                                    break;
                                case 3:
                                    $chance = 13;
                                    $dmgdecrease = mt_rand(12, 15);
                                    break;
                                case 4:
                                    $chance = 16;   
                                    $dmgdecrease = mt_rand(15, 18); 
                                    break;
                                case 5:
                                    $chance = 21;
                                    $dmgdecrease = mt_rand(18, 22);    
                                    break;
                            }

                            if (mt_rand(1, 100) <= $chance) {
                                if (!$this->isSilenced($victim)) {
                                    if ($victimSession->getCooldown("valor") === 0 || $victimSession->getCooldown("valor") === null) {
                                        $heldItem = $victim->getInventory()->getItemInHand();
                                        if ($heldItem instanceof Sword) {
                                            $event->setBaseDamage($event->getBaseDamage() * (1 - ($dmgdecrease / 100)));
                                            $victimSession->addCooldown("valor", $cooldown);
                                        }
                                    }
                                }
                            }
                                
                        } elseif ($enchantment instanceof HeavyEnchantment) { 
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;
                            $cooldown = 4;
                            $dmgdecrease = 0;

                            switch ($level) {
                                case 1:
                                    $chance = 4;
                                    $dmgdecrease = 2;
                                    break;
                                case 2:
                                    $chance = 9;
                                    $dmgdecrease = 4;
                                    break;
                                case 3:
                                    $chance = 12;  
                                    $dmgdecrease = 6;
                                    break;
                                case 4:
                                    $chance = 16;
                                    $dmgdecrease = 8;
                                    break;
                                case 5:
                                    $chance = 21;
                                    $dmgdecrease = 10;  
                                    break;
                            }

                            if (mt_rand(1, 100) <= $chance) {
                                if (!$this->isSilenced($victim)) {
                                    $cause = $victim->getLastDamageCause();
                                    if ($cause instanceof EntityDamageByEntityEvent && $cause->getCause() === EntityDamageByEntityEvent::CAUSE_PROJECTILE) {
                                        $projectile = $cause->getDamager();
                                        if ($projectile instanceof Arrow) {
                                            $currentDamage = $event->getBaseDamage();
                                            $newDamage = $currentDamage - $dmgdecrease;
                                            if ($newDamage < 0) {
                                                $newDamage = 0;
                                            }
                                            $event->setBaseDamage($newDamage);
                                        }
                                    }
                                }
                            }
                        } elseif ($enchantment instanceof PoisonedEnchantment) {
                        
                            if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 5) {
                                $cause = $victim->getLastDamageCause();
                                if ($cause instanceof EntityDamageByEntityEvent && $cause->getCause() === EntityDamageByEntityEvent::CAUSE_PROJECTILE) {
                                    $projectile = $cause->getDamager();
                                    if ($projectile instanceof Arrow) {
                                        $currentDamage = $event->getBaseDamage();
                                        $newDamage = $currentDamage - ($enchantmentInstance->getLevel() * 2);
                                        if ($newDamage < 0) {
                                            $newDamage = 0;
                                        }
                                        $event->setBaseDamage($newDamage);
                        
                                        $chance = 20; 
                                        if (mt_rand(1, 100) <= $chance) {
                                            $attacker = $cause->getDamager();
                                            if ($attacker instanceof Living) {
                                                $attacker->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), Utils::secondsToTicks(3), $enchantmentInstance->getLevel()));
                                            }
                                        }
                                    }
                                }
                            }
                        } elseif ($enchantment instanceof MirroredEnchantment) {
                            if (mt_rand(1, 100) < $enchantmentInstance->getLevel() * 2) {
                                $reflectionChance = $enchantmentInstance->getLevel() * 2; 
            
                                if (mt_rand(1, 100) <= $reflectionChance) {
                                    $reflectedDamage = $event->getFinalDamage();
                                    $event->getDamager()->attack(new EntityDamageByEntityEvent($victim, $player, $event->getCause(), $reflectedDamage));
                                }
                            }
                        } elseif ($enchantment instanceof ClarityEnchantment) {
                            $level = $enchantmentInstance->getLevel();
    
                            if ($level >= 1) {
                                if ($victim->getEffects()->has(VanillaEffects::BLINDNESS())) {
                                    $victim->getEffects()->remove(VanillaEffects::BLINDNESS());
                                }
                            }
                            if ($level >= 2) {
                                if ($victim->getEffects()->has(VanillaEffects::NAUSEA())) {
                                    $victim->getEffects()->remove(VanillaEffects::NAUSEA());
                                }
                            }
                        }    
                    }
                }
        }
    }
    
    private function cancelTasksForPlayer(string $playerName): void
    {
        if (isset(EnchantListener::$activeAngelicTasks[$playerName])) {
            $players = EnchantListener::$activeAngelicTasks[$playerName];
            foreach ($players as $player) {
                $healingTask = new AngelicHealingTask($player, 0.5);
                $healingTask->getHandler()->cancel();
            }
            unset(EnchantListener::$activeAngelicTasks[$playerName]);
        }
    }

    public function onDeath(EntityDeathEvent $event): void
    {
        $entity = $event->getEntity();
        $cause = $entity->getLastDamageCause();

        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();

            if ($damager instanceof Player) {
                $item = $damager->getInventory()->getItemInHand();
                $enchantments = $item->getEnchantments();

                foreach ($enchantments as $enchantmentInstance) {
                    $enchantment = $enchantmentInstance->getType();

                    if ($enchantment instanceof InquisitiveEnchantment) {
                        $level = $enchantmentInstance->getLevel();
                        $chance = 0;
                        $cooldown = 4;

                        switch ($level) {
                            case 1:
                                $chance = 30;
                                break;
                            case 2:
                                $chance = 35;
                                break;
                            case 3:
                                $chance = 40;
                                break;
                            case 4:
                                $chance = 45;
                                break;
                        }

                        if (mt_rand(1, 100) <= $chance) {
                            if (!$this->getCooldown($damager)) {
                                $event->setXpDropAmount($event->getXpDropAmount() * (1.0 + 0.25 * $level));
                                $this->setCooldown($damager, $cooldown);
                            }
                        }
                    }

                    if ($entity instanceof Player) {
                        if ($enchantment instanceof DecapitationEnchantment) {
                            $level = $enchantmentInstance->getLevel();
                            $chance = 0;

                            switch ($level) {
                                case 1:
                                    $chance = 20;
                                    break;
                                case 2:
                                    $chance = 40;
                                    break;
                                case 3:
                                    $chance = 60;
                                    break;
                            }

                            if (mt_rand(1, 100) <= $chance) {
                                $entity->getWorld()->dropItem($entity->getPosition(), Main::$instance->getHeadItem($entity->getSkin(), $entity->getName()));
                            }
                        }
                    }
                }
            }
        }
    }

    public function onItemHeld(PlayerItemHeldEvent $event): void
    {
        $player = $event->getPlayer();
        $previousItem = $player->getInventory()->getItemInHand();
        $newItem = $event->getItem();

        if (EnchantUtils::hasHasteEnchantment($previousItem)) {
            $player->getEffects()->remove(VanillaEffects::HASTE());
        } elseif (EnchantUtils::hasHasteEnchantment($newItem)) {
            $hasteLevel = EnchantUtils::getHasteLevel($newItem);
            $player->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), 20 * 9999, $hasteLevel, false));
        }

    }

    public function onTransaction(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
    
        $effectsMap = [
            "drunk" => [
                VanillaEffects::SLOWNESS(),
                VanillaEffects::STRENGTH(),
                VanillaEffects::MINING_FATIGUE(),
            ],
            "aquatic" => [VanillaEffects::WATER_BREATHING()],
            "glowing" => [VanillaEffects::NIGHT_VISION()],
            "rocket_boots" => [VanillaEffects::SPEED()],
            "overload" => [VanillaEffects::HEALTH_BOOST()],
            "bounce" => [VanillaEffects::JUMP_BOOST()],
            "lavabound" => [VanillaEffects::FIRE_RESISTANCE()],
        ];
    
        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                $inventory = $action->getInventory();
                if ($inventory instanceof ArmorInventory) {
                    $newArmorPiece = $action->getTargetItem();
                    $oldArmorPiece = $action->getSourceItem();
    
                    foreach ($effectsMap as $enchantmentType => $effects) {
                        $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentType);
                        if ($enchantment !== null) {
                            if ($oldArmorPiece->hasEnchantment($enchantment)) {
                                foreach ($effects as $effect) {
                                    $player->getEffects()->remove($effect);
                                }
                            }
                        }
                    }
    
                    foreach ($effectsMap as $enchantmentType => $effects) {
                        $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentType);
                        if ($enchantment !== null) {
                            if ($newArmorPiece->hasEnchantment($enchantment)) {
                                $enchantmentLevel = $newArmorPiece->getEnchantment($enchantment)->getLevel();
                                foreach ($effects as $effect) {
                                    $player->getEffects()->add(new EffectInstance($effect, 20 * 9999, $enchantmentLevel - 1, false));
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    

    private function adjustEffectLevel($enchantmentType, $level, $effect)
    {
        switch ($enchantmentType) {
            case "drunk":
                return $level - 1;
            case "overload":
                return $level - 1;   
            case "rocket_boots":
                return $level - 1;     
            case "bounce":
                return $level - 1;    
            default:
                return $level;
        }
    }

    public function onRestoreFood(PlayerExhaustEvent $event): void {
        $player = $event->getPlayer();
        
        if (!$event->isCancelled()) {
            $armorItems = $player->getArmorInventory()->getContents();
            
            foreach ($armorItems as $item) {
                if ($item instanceof Item) {
                    $enchantments = $item->getEnchantments();
                    foreach ($enchantments as $enchInstance) {
                        $ench = $enchInstance->getType();
                        if ($ench instanceof WellfedEnchantment) {
                            $level = $enchInstance->getLevel();
                    
                            $foodToRestore = $level;
                            
                            $currentFood = $player->getHungerManager()->getFood();
                            $maxFood = $player->getHungerManager()->getMaxFood();
                            $newFood = min($currentFood + $foodToRestore, $maxFood);
                            
                            $player->getHungerManager()->setFood($newFood);
                            $player->getHungerManager()->setExhaustion($player->getHungerManager()->getExhaustion() - $foodToRestore * 0.1);
                        }
                    }
                }
            }
        }
    }

    private function applyBleedEffect(Player $victim, float $dmg, int $times): void {
        $count = 0;
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            function() use ($victim, $dmg, &$count, $times): void {
                if ($victim->isAlive() && $count < $times - 1) {
                    $victim->setHealth($victim->getHealth() - $dmg);
                    $victim->getWorld()->addParticle($victim->getPosition()->asVector3(), new BlockBreakParticle(VanillaBlocks::REDSTONE()));
                    $count++;
                }
            }
        ), 20);
    }

    public function isSilenced(Player $player): bool {
        $playerName = $player->getName();
        return isset($this->silenced[$playerName]) && $this->silenced[$playerName] > time();
    }
}
