<?php

namespace xtcy\ElysiumCore\commands\balance;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\Server;
use xtcy\ElysiumCore\Loader;

class BalanceTopCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new IntegerArgument("page", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();
    
        $playerBalances = [];
        foreach ($onlinePlayers as $player) {
            $session = Loader::getPlayerManager()->getSession($player);
            $balance = $session->getBalance(); 
            $playerBalances[] = ['player' => $player, 'balance' => $balance];
        }
    
        usort($playerBalances, function ($a, $b) {
            return $b['balance'] <=> $a['balance'];
        });
    
        $page = isset($args["page"]) ? max(1, (int)$args["page"]) : 1;
        $perPage = 10;
        $totalPlayers = count($playerBalances);
        $maxPage = (int)ceil($totalPlayers / $perPage);
        $start = ($page - 1) * $perPage;
        $end = min($start + $perPage, $totalPlayers);
    
        $header = C::colorize("&r&l&5Top Online Player Balances: (&f$page&5/&f$maxPage&5)");
        $sender->sendMessage($header);
    
        for ($i = $start; $i < $end; $i++) {
            $rank = $i + 1;
            $player = $playerBalances[$i]['player'];
            $balance = number_format($playerBalances[$i]['balance'], 0, '.', ','); 
            $message = C::colorize("&r&l&d$rank. &r&f{$player->getName()} &7 - &l&d\$$balance");
            $sender->sendMessage($message);
        }
    
        if ($end == $start) {
            $sender->sendMessage(C::colorize("&r&cNo players to display on this page."));
        }
    }

    public function getPermission(): string
    {
        return "command.default";
    }
}
