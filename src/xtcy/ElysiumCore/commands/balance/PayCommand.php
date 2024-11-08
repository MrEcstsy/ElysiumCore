<?php

namespace xtcy\ElysiumCore\commands\balance;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;

class PayCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
        $this->registerArgument(2, new TextArgument("reason", true));
    }
    
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        $targetName = $args["name"] ?? null;
        $amount = $args["amount"] ?? null;

        if ($targetName === null || $amount === null) {
            $sender->sendMessage(C::RED . "Usage: " . $this->getUsage());
            return;
        }

        $target = Utils::getPlayerByPrefix($targetName);
        if ($target === null) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cNo player named &f" . $args["name"] . " &chas played before."));
            return;
        }

        $senderSession = Loader::getPlayerManager()->getSession($sender);
        $targetSession = Loader::getPlayerManager()->getSession($target);

        if ($senderSession === null || $targetSession === null) {
            $sender->sendMessage(C::colorize("&r&7(/pay) &c✘ Failed to process payment. Please try again later."));
            return;
        }

        if ($senderSession->getBalance() < $amount) {
            $sender->sendMessage(C::colorize("&r&l&c(!) &r&cYou do not have enough money to pay " . $target->getNameTag() . "&c $" . number_format($args["amount"])));
            return;
        }

        $senderSession->subtractBalance($amount);

        $targetSession->addBalance($amount);

        $formattedAmount = number_format($amount);

        if (isset($args["reason"])) {
            $message = C::colorize("&r&l&a(!) &r&aYou paid " . $target->getNameTag() . "&a: $" . number_format($args["amount"]) . " &r&afor '&f" . $args["reason"] . "&a'");
            $sender->sendMessage($message);
            $target->sendMessage(C::colorize("&r&l&a(!) &r&aYou received $" . $formattedAmount . " from " . $sender->getNameTag() . " &afor '&f" . $args["reason"] . "&a'"));
        } else {
            $message = C::colorize("&r&l&a(!) &r&aYou paid " . $target->getNameTag() . "&a: &a$" . number_format($args["amount"]));
            $sender->sendMessage($message);
            $target->sendMessage(C::colorize("&r&l&a(!) &r&aYou received §a$" . $formattedAmount . " §sfrom " . $sender->getNameTag()));
        }

    }

    public function getPermission(): string {
        return "command.default";
    }
}