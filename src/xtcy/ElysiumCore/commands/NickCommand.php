<?php

namespace xtcy\ElysiumCore\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\essentialsx\Loader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class NickCommand extends BaseCommand {
    
    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }
    
        $nickname = implode(" ", $args);
        
        $maxNickLength = 15;
        $nicknamePrefix = '~';
        $nickBlacklist = [];
        $ignoreColorsInMaxNickLength = false;
    
        if (!$ignoreColorsInMaxNickLength) {
            $nickname = TextFormat::clean($nickname);
        }
    
        $nicknameLength = strlen($nickname);
        if ($nicknameLength > $maxNickLength) {
            $sender->sendMessage(C::RED . "Nickname cannot exceed $maxNickLength characters.");
            return;
        }
    
        foreach ($nickBlacklist as $blacklisted) {
            if (stripos($nickname, $blacklisted) !== false) {
                $sender->sendMessage(C::RED . "Nickname contains blacklisted phrase: $blacklisted");
                return;
            }
        }
    
        if ($sender->hasPermission("essentialsx.nick.hideprefix")) {
            $sender->setDisplayName($nickname);
        } else {
            $sender->setDisplayName($nicknamePrefix . $nickname);
        }
        $sender->sendMessage(C::GREEN . "Your nickname has been set to: " . $nicknamePrefix . $nickname);
    }

    public function getPermission(): string {
        return "command.nick";
    }
}