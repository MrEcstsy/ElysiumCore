<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use MakayaYoel\Slotbot\_constants\SlotbotItemIdentifiers;
use MakayaYoel\Slotbot\utils\Utils as SBUtils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\utils\Utils;

class TicketsCommand extends BaseCommand {

    public function prepare() : void {
        $this->setPermission("command.admin");

        $this->registerArgument(0, new IntegerArgument("amount", false));
        $this->registerArgument(1, new RawStringArgument("player", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }
    
        $amount = $args["amount"] ?? 1; // Default amount to 1 if not specified
    
        if (isset($args["player"])) {
            $playerName = $args["player"];
            $targetPlayer = Utils::getPlayerByPrefix($playerName);
            if ($targetPlayer !== null) {
                $targetInventory = $targetPlayer->getInventory();
                $ticketItem = SBUtils::getItem(SlotbotItemIdentifiers::SLOTBOT_TICKET)->setCount($amount);
                if ($targetInventory->canAddItem($ticketItem)) {
                    $targetInventory->addItem($ticketItem);
                    $sender->sendMessage(TextFormat::colorize("&r&l&a(!) &r&aSuccessfully given " . $amount . "x tickets to " . $playerName));
                    return;
                } else {
                    $sender->sendMessage(TextFormat::colorize("&r&l&a(!) &r&cThe inventory of " . $playerName . " is full."));
                    return;
                }
            } else {
                $sender->sendMessage(TextFormat::colorize("&r&l&a(!) &r&cPlayer not found."));
                return;
            }
        }
    
        // If no player is specified, give tickets to the sender
        $senderInventory = $sender->getInventory();
        $ticketItem = SBUtils::getItem(SlotbotItemIdentifiers::SLOTBOT_TICKET)->setCount($amount);
        if ($senderInventory->canAddItem($ticketItem)) {
            $senderInventory->addItem($ticketItem);
            $sender->sendMessage(TextFormat::colorize("&r&l&a(!) &r&aSuccessfully given " . $amount . "x tickets to yourself."));
        } else {
            $sender->sendMessage(TextFormat::colorize("&r&l&a(!) &r&cYour inventory is full."));
        }
    }
    
}