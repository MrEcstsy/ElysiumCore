<?php

namespace xtcy\ElysiumCore\utils\Menu;

use MakayaYoel\Slotbot\_constants\SlotbotItemIdentifiers;
use MakayaYoel\Slotbot\utils\Utils as SlotUtils;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use Vecnavium\FormsUI\SimpleForm;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\items\Items;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\ElysiumUtils;

class QuestUI {

    public static array $availableQuests = [
        [
            'name' => 'Mob Hunter I',
            'description' => 'Kill 10 mobs of any type.',
            'progress' => 0,
            'target' => 10,
            'target_type' => 'mobs',
            'task' => 'kill',
            'reward' => 1,
            "status" => "uncompleted"
        ],
        [
            'name' => 'Mob Hunter II',
            'description' => 'Kill 30 mobs of any type.',
            'progress' => 0,
            'target' => 30,
            'target_type' => 'mobs',
            'task' => 'kill',
            'reward' => 2,
            "status" => "uncompleted"
        ],
        [
            'name' => 'Mob Hunter III',
            'description' => 'Kill 60 mobs of any type.',
            'progress' => 0,
            'target' => 60,
            'target_type' => 'mobs',
            'task' => 'kill',
            'reward' => 3,
            "status" => "uncompleted"
        ],
        [
            'name' => 'Wheat Harvester I',
            'description' => 'Harvest 20 wheat.',
            'progress' => 0,
            'target' => 20,
            'target_type' => BlockTypeIds::WHEAT,
            'task' => 'harvest',
            'reward' => 1,
            "status" => "uncompleted"
        ],
        [
            'name' => 'Wheat Harvester II',
            'description' => 'Harvest 50 wheat.',
            'progress' => 0,
            'target' => 50,
            'target_type' => BlockTypeIds::WHEAT,
            'task' => 'harvest',
            'reward' => 2,
            "status" => "uncompleted"
        ],
        [
            'name' => 'Wheat Harvester III',
            'description' => 'Harvest 100 wheat.',
            'progress' => 0,
            'target' => 100,
            'target_type' => BlockTypeIds::WHEAT,
            'task' => 'harvest',
            'reward' => 3,
            "status" => "uncompleted"
        ],
        [
            'name' => 'Explorer I',
            'description' => 'Travel 1000 blocks.',
            'progress' => 0,
            'target' => 1000,
            'target_type' => 'distance',
            'task' => 'travel',
            'reward' => 1,
            "status" => "uncompleted"
        ],
        [
            'name' => 'Explorer II',
            'description' => 'Travel 3000 blocks.',
            'progress' => 0,
            'target' => 3000,
            'target_type' => 'distance',
            'task' => 'travel',
            'reward' => 2,
            "status" => "uncompleted"
        ],
        [
            'name' => 'Explorer III',
            'description' => 'Travel 5000 blocks.',
            'progress' => 0,
            'target' => 5000,
            'target_type' => 'distance',
            'task' => 'travel',
            'reward' => 3,
            "status" => "uncompleted"
        ],
        [
            'name' => 'XP Grinder I',
            'description' => 'Gain 1000 xp.',
            'progress' => 0,
            'target' => 1000,
            'target_type' => 'xp',
            'task' => 'gain_xp',
            'reward' => 1,
            "status" => "uncompleted"
        ],
        [
            'name' => 'XP Grinder II',
            'description' => 'Gain 3000 xp.',
            'progress' => 0,
            'target' => 3000,
            'target_type' => 'xp',
            'task' => 'gain_xp',
            'reward' => 2,
            "status" => "uncompleted"
        ],
        [
            'name' => 'XP Grinder III',
            'description' => 'Gain 5000 xp.',
            'progress' => 0,
            'target' => 5000,
            'target_type' => 'xp',
            'task' => 'gain_xp',
            'reward' => 3,
            "status" => "uncompleted"
        ],
        [
            'name' => 'XP Spender I',
            'description' => 'Spend 1000 xp.',
            'progress' => 0,
            'target' => 1000,
            'target_type' => 'xp',
            'task' => 'spend_xp',
            'reward' => 1,
            "status" => "uncompleted"
        ],
        [
            'name' => 'XP Spender II',
            'description' => 'Spend 3000 xp.',
            'progress' => 0,
            'target' => 3000,
            'target_type' => 'xp',
            'task' => 'spend_xp',
            'reward' => 2,
            "status" => "uncompleted"
        ],
        [
            'name' => 'XP Spender III',
            'description' => 'Spend 5000 xp.',
            'progress' => 0,
            'target' => 5000,
            'target_type' => 'xp',
            'task' => 'spend_xp',
            'reward' => 3,
            "status" => "uncompleted"
        ],
    ];

    public static function getMainQuestForm(Player $player): SimpleForm
    {
        $session = Loader::getInstance()->getPlayerManager()->getSession($player);
        $form = new SimpleForm(function(Player $player, $data) use ($session): void {
            if ($data === null) {
                return;
            }
    
            $questNames = array_keys($session->getAllQuests());
            $questName = $questNames[$data] ?? null;
            if ($questName !== null) {
                $quest = $session->getQuest($questName);
                if ($quest !== null) {
                    $player->sendForm(self::getQuestDetailForm($player, $questName, $quest));
                }
            }
        });
    
        $form->setTitle(C::colorize("&r&8Quests"));
        $form->setContent(C::colorize("&r&7Click a quest to view more information!\n&r&7Quests change every 12 hours.\n\n&r&dYour Quest Tokens: &f" . number_format($session->getQuestTokens())));
    
        $questNames = $session->getAllQuests();
        if ($session->getCooldown("quest_reset") === 0 || $session->getCooldown("quest_reset") === null) {
            $session->addCooldown("quest_reset", 43200);
    
            shuffle(self::$availableQuests);
            $selectedQuests = array_slice(self::$availableQuests, 0, 5);
    
            foreach ($selectedQuests as $questData) {
                $session->addQuest($questData['name'], $questData);
                $form->addButton(C::colorize("&r&8&l" . $questData['name']));
            }
        } else {
            foreach ($questNames as $questName => $quest) {
                $form->addButton(C::colorize("&r&8&l" . $questName));
            }
        }
    
        return $form;
    }

    public static function getMainQuestInventory(Player $player): InvMenu {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST);
        $inventory = $menu->getInventory();
        $session = Loader::getPlayerManager()->getSession($player);

        $menu->setName(C::colorize("&r&8Quests"));

        Utils::fillInventory($inventory, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK())->asItem(), [20, 30, 22, 32, 24, 49]);
        $questNames = $session->getAllQuests();

        if ($session->getCooldown("quest_reset") === 0 || $session->getCooldown("quest_reset") === null) {
            $session->addCooldown("quest_reset", 43200);
            shuffle(self::$availableQuests);
            $selectedQuests = array_slice(self::$availableQuests, 0, 5);
            foreach ($selectedQuests as $questData) {
                $session->addQuest($questData['name'], $questData);
                $inventory->addItem(VanillaItems::PAPER()->setCustomName(C::colorize("&r&l" . $questData['name']))->setLore([
                    C::colorize("&r&7" . $questData['description']),
                    C::colorize("&r&7Target: " . $questData['target'] . " " . (is_int($questData['target_type']) ? Utils::convertBlockTypeIdToName($questData['target_type']) : ucfirst($questData['target_type']))),
                    C::colorize("&r&7Progress: " . $questData['progress'] . "/" . $questData['target']),
                    C::colorize("&r&7Reward: " . $questData['reward'] . " Quest Tokens"),
                    C::colorize("&r&7Status: " . ucfirst($questData['status'])),
                    "",
                    C::colorize("&r&7Click to claim once completed.")
                ]));
            }
        } else {
            foreach ($questNames as $questName => $quest) {
                $inventory->addItem(VanillaItems::PAPER()->setCustomName(C::colorize("&r&l" . $questName))->setLore([
                    C::colorize("&r&7" . $quest['description']),
                    "",
                    C::colorize("&r&7Target: " . $quest['target'] . " " . (is_int($quest['target_type']) ? Utils::convertBlockTypeIdToName($quest['target_type']) : ucfirst($quest['target_type']))),
                    C::colorize("&r&7Progress: " . $quest['progress'] . "/" . $quest['target']),
                    C::colorize("&r&7Reward: " . $quest['reward'] . " Quest Tokens"),
                    C::colorize("&r&7Status: " . ucfirst($quest['status'])),
                    "",
                    C::colorize("&r&7Click to claim once completed.")
                ]));
            }
        }

        $inventory->setItem(49, VanillaItems::NETHER_STAR()->setCustomName(C::colorize("&r&dQuests"))->setLore([
            C::colorize("&r&7Quests change every 12 hours."),
            "",
            C::colorize("&r&dYour Quest Tokens: &f" . number_format($session->getQuestTokens()))
        ]));

        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($session): void {
            $player = $transaction->getPlayer();
            $item = $transaction->getItemClicked();
        
            $clickedSlot = $transaction->getAction()->getSlot();
            if (in_array($clickedSlot, [20, 30, 22, 32, 24])) {
                $questName = C::clean($item->getCustomName());
                $quest = $session->getQuest($questName);
                if ($quest === null) {
                    $player->sendMessage(C::colorize("&r&cQuest not found."));
                    return;
                }
    
                if ($quest['status'] === 'completed') {
                    $player->sendToastNotification(C::colorize("&r&l&dEthereal &fNetwork"), C::colorize("&r&l&a(!) &r&fQuest has already been completed!"));
                    return;
                }
    
                if ($quest['progress'] === $quest['target']) {
                    $session->completeQuest($questName);
                    $session->addQuestTokens($quest['reward']);
                    $player->sendMessage(C::colorize("&r&l&a(!) &r&aQuest completed and reward claimed!"));
                    $player->removeCurrentWindow();
                } else {
                    $player->sendToastNotification(C::colorize("&r&l&dEthereal &fNetwork"), C::colorize("&r&fQuest is still in progress..."));
                }
            }

            if ($transaction->getAction()->getSlot() === 49) {
                $player->removeCurrentWindow();
                $player->sendForm(self::getQuestShop($player));
                return;
            }
        }));
        
        return $menu;
    }
    
    public static function getQuestDetailForm(Player $player, string $questName, array $quest): SimpleForm
    {
        $form = new SimpleForm(function(Player $player, $data) use ($questName, $quest): void {
            if ($data === null) {
                return;
            }
    
            $session = Loader::getInstance()->getPlayerManager()->getSession($player);
    
            if ($quest['status'] === 'completed') {
                $player->sendMessage(C::colorize("&r&l&a(!) &r&aQuest has already been completed!"));
                return;
            }

            if ($quest['progress'] === $quest['target']) {
                $player->sendMessage(C::colorize("&r&l&a(!) &r&aQuest completed!"));
                $session->completeQuest($questName);
                $session->addQuestTokens($quest['reward']);
            } else {
                $player->sendMessage(C::colorize("&r&l&6(!) &r&6Quest in progress..."));
            }
        });
    
        $form->setTitle(C::colorize("&r&8&l" . $questName));
        $form->setContent(C::colorize(
            "&r&7Description: &r&f" . $quest['description'] . "\n\n" .
            "&r&7Progress: &r&f" . $quest['progress'] . "/" . $quest['target'] . "\n" .
            "&r&7Reward: &r&f" . $quest['reward'] . " Quest Tokens" . "\n" .
            "&r&7Status: &r&f" . ucfirst($quest['status'])
        ));
        $form->addButton(C::colorize("&r&l&8Submit"));
    
        return $form;
    }
    
    public static function getQuestShop(Player $player): SimpleForm
    {
        $form = new SimpleForm(function(Player $player, $data): void {
            if ($data === null) {
                return;
            }       

            $session = Loader::getInstance()->getPlayerManager()->getSession($player);

            switch ($data) {
                case 0:
                    if ($session->getQuestTokens() >= 1) {
                        $session->addBalance(50000);
                        $player->sendMessage(C::colorize("&r&aSuccessfully purchased x1 $50,000 for 1 Quest Token(s)!"));
                    } else {
                        $player->sendToastNotification(C::colorize("&r&c&l(!) &r&cInsufficient Funds!"), C::colorize("&r&7You need at least 1 quest token to buy this item. You have: " . number_format($session->getQuestTokens())));
                    }
                    break;
                case 1:
                    $player->sendForm(ElysiumUtils::sendQuestPurchaseConfirmation($player, Items::createLootbox("stormcaller"), 15));
                    break;
                case 2:
                    $player->sendForm(ElysiumUtils::sendQuestPurchaseConfirmation($player, Items::getEnchantScrolls("transmog"), 2));
                    break;
                case 3:
                    $player->sendForm(ElysiumUtils::sendQuestPurchaseConfirmation($player, SlotUtils::getItem(SlotbotItemIdentifiers::SLOTBOT_TICKET), 8));
                    break;
                case 4:
                    $player->sendForm(ElysiumUtils::sendQuestPurchaseConfirmation($player, Items::getEnchantScrolls("depth_strider"), 10));
                    break;
                case 5:
                    $player->sendForm(ElysiumUtils::sendQuestPurchaseConfirmation($player, Items::getEnchantScrolls("thorns"), 10));
                    break;
                case 6:
                    $player->sendForm(ElysiumUtils::sendQuestPurchaseConfirmation($player, Items::getEnchantScrolls("fortune"), 10));
                    break;
                case 7:
                    $player->sendForm(ElysiumUtils::sendQuestPurchaseConfirmation($player, Items::createBossEgg("broodmother"), 10));
                    break;
            }

        });

        $form->setTitle(C::colorize("&r&8&lQuest Shop"));
        $items = [
            "&r&l&e$50,000 \n &r&aBuy Price: 1 token/ea",
            "&r&l&eStormcaller Lootbox \n &r&aBuy Price: 15 token/ea",
            "&r&l&eTransmog Scroll \n &r&aBuy Price: 2 token/ea",
            "&r&l&eSlotbot Ticket \n &r&aBuy Price: 8 token/ea",
            "&r&l&eDepth Strider \n &r&aBuy Price: 10 token/ea",
            "&r&l&eThorns \n &r&aBuy Price: 10 token/ea",
            "&r&l&eFortune \n &r&aBuy Price: 10 token/ea",
            "&r&l&eBroodmother \n &r&aBuy Price: 10 token/ea",
        ];

        foreach ($items as $key) {
            $form->addButton(C::colorize($key));
        }
        return $form;
    }
}