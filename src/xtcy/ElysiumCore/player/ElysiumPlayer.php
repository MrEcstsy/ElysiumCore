<?php

declare(strict_types=1);

namespace xtcy\ElysiumCore\player;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Ramsey\Uuid\UuidInterface;
use wockkinmycup\utilitycore\utils\Utils;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\player\settings\PlayerSettings;
use xtcy\ElysiumCore\utils\ElysiumUtils;
use xtcy\ElysiumCore\utils\Queries;

final class ElysiumPlayer
{

    private bool $isConnected = false;

    public function __construct(
        private UuidInterface $uuid,
        private string        $username,
        private int           $balance,
        private int           $gems,
        private int           $kills,
        private int           $deaths,
        private int           $bounty,
        private string        $cooldowns,
        private string        $title,
        private int           $level,
        private string        $settings,
        private int           $slotCredits,
        private int           $questtokens,
        private string        $quests
    )
    {
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function setConnected(bool $connected): void
    {
        $this->isConnected = $connected;
    }

    /**
     * Get UUID of the player
     *
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * This function gets the PocketMine player
     *
     * @return Player|null
     */
    public function getPocketminePlayer(): ?Player
    {
        return Server::getInstance()->getPlayerByUUID($this->uuid);
    }

    /**
     * Get username of the session
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username of the session
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
        $this->updateDb(); // Make sure to call updateDb function when you're making changes to the player data
    }

    /**
     * @return int
     */
    public function getBalance(): int
    {
        return $this->balance;
    }

    /**
     * @param int $amount
     * @return void
     */
    public function addBalance(int $amount, bool $force = false): void
    {
        $maxAmount = 1000000000;

        $remainingAmount = $maxAmount - $this->balance;
        $amountToAdd = min($amount, $remainingAmount);

        if ($amountToAdd <= 0) {
            $this->getPocketminePlayer()->sendMessage(TextFormat::colorize("&r&c&l(!) &r&cThis will exceed the maximum amount of money."));
            return;
        }

        $this->balance += $amountToAdd;
        $this->getPocketminePlayer()->sendMessage(TextFormat::colorize("&r&l&a+ &r&a$" . number_format($amountToAdd)));
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * @param int $amount
     * @return void
     */
    public function subtractBalance(int $amount, bool $force = false): void
    {

        $this->balance -= $amount;
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * @param int $amount
     * @return void
     */
    public function setBalance(int $amount): void
    {
        $this->balance = $amount;
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * @return int
     */
    public function getGems(): int
    {
        return $this->gems;
    }

    /**
     * @param int $amount
     * @return void
     */
    public function addGems(int $amount): void
    {
        $maxAmount = 100000000;

        $remainingAmount = $maxAmount - $this->gems;
        $amountToAdd = min($amount, $remainingAmount);

        if ($amountToAdd <= 0) {
            $this->getPocketminePlayer()->sendMessage(TextFormat::colorize("&r&c&l(!) &r&cThis will exceed the maximum amount of gems."));
            return;
        }

        $this->gems += $amountToAdd;
        $this->getPocketminePlayer()->sendMessage(TextFormat::colorize("&r&l&a+&r&a " . number_format($amountToAdd)));
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * @param int $amount
     * @return void
     */
    public function subtractGems(int $amount): void
    {
        $this->gems -= $amount;
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * @param int $amount
     * @return void
     */
    public function setGems(int $amount): void
    {
        $this->gems = $amount;
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * Get kills of the session
     *
     * @return int
     */
    public function getKills(): int
    {
        return $this->kills;
    }

    /**
     * Add kills to the session
     *
     * @param int $amount
     * @return void
     */
    public function addKills(int $amount = 1): void
    {
        $this->kills += $amount;
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * @return int
     */
    public function getDeaths(): int {
        return $this->deaths;
    }

    /**
     * @param int $amount
     * @return void
     */
    public function addDeaths(int $amount = 1): void {
        $this->deaths += $amount;
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * @return int
     */
    public function getBounty(): int {
        return $this->bounty;
    }

    /**
     * @param int $amount
     * @return void
     */
    public function addBounty(int $amount = 1): void {
        $this->bounty += $amount;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function removeBounty(): void {
        $this->bounty -= 0;
        $this->updateDb();
    }

    public function addEXP(int $amount = 1): void
    {
        $this->getPocketminePlayer()->getXpManager()->addXp($amount);
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
    }

    public function setEXP(int $amount = 0): void
    {
        $this->getPocketminePlayer()->getXpManager()->setCurrentTotalXp($amount);
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
    }

    public function subtractEXP(int $amount = 0): void
    {
        $this->getPocketminePlayer()->getXpManager()->subtractXp($amount);
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
    }

    public function addCooldown(string $cooldownName, int $duration): void
    {
        $cooldowns = json_decode($this->cooldowns, true) ?? [];

        $cooldowns[$this->getUuid()->toString()][$cooldownName] = time() + $duration;

        $this->cooldowns = json_encode($cooldowns);

        $this->updateDb();
    }

    public function getCooldown(string $cooldownName): ?int
    {
        $cooldowns = json_decode($this->cooldowns, true);

        if ($cooldowns !== null && isset($cooldowns[$this->getUuid()->toString()][$cooldownName])) {
            $cooldownExpireTime = $cooldowns[$this->getUuid()->toString()][$cooldownName];
            $remainingCooldown = $cooldownExpireTime - time();
            return max(0, $remainingCooldown);
        }

        return null;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
        $this->updateDb();
    }

    public function getLevel(): int {
        return $this->level;
    }

    public function addLevel(int $amount = 1): void {
        
        $maxAmount = 20;

        $remainingAmount = $maxAmount - $this->level;
        $amountToAdd = min($amount, $remainingAmount);

        if ($amountToAdd <= 0) {
            $this->getPocketminePlayer()->sendMessage(TextFormat::colorize("&r&c&l(!) &r&cThis will exceed the max level."));
            return;
        }

        $this->level += $amountToAdd;
        $this->getPocketminePlayer()->sendMessage(TextFormat::colorize("&r&l&a+&r&a" . number_format($amountToAdd) . "level(s)"));
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    public function subtractLevel(int $amount = 1): void {
        $this->level -= $amount;
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    public function setLevel(int $amount = 0): void {
        $this->level = $amount;
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        $this->updateDb();
    }

    /**
     * Get all settings and their values for the player
     *
     * @return array Associative array of settings and their values
     */
    public function getAllSettings(): array
    {
        return json_decode($this->settings, true) ?? [];
    }


    /**
     * Get a specific setting value by key
     *
     * @param string $key
     * @return mixed|null The value of the setting if found, or null if the key doesn't exist
     */
    public function getSetting(string $key): mixed
    {
        $decodedSettings = json_decode($this->settings, true);
        return $decodedSettings[$key] ?? null;
    }


    /**
     * Set a specific setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setSetting(string $key, mixed $value): void
    {
        $decodedSettings = json_decode($this->settings, true);
        $decodedSettings[$key] = $value;
        $this->settings = json_encode($decodedSettings);
        $this->updateDb();
    }

    public function getKDRRatio(): float {
        ElysiumUtils::sendUpdate($this->getPocketminePlayer());
        return $this->deaths > 0 ? round($this->kills / $this->deaths, 2) : $this->kills;
    }

    public function getSlotCredits(): int {
        return $this->slotCredits;
    }

    public function setSlotCredits(int $amount = 1): void {
        $this->slotCredits = $amount;
        $this->updateDb();
    }

    public function subtractSlotCredits(int $amount = 1): void {
        $this->slotCredits -= $amount;
        $this->updateDb();
    }

    public function addSlotCredits(int $amount = 1): void {
        $this->slotCredits += $amount;
        $this->updateDb();
    }

    public function getQuestTokens(): int {
        return $this->questtokens;
    }

    public function setQuestTokens(int $amount = 1): void {
        $this->questtokens = $amount;
    }

    public function addQuestTokens(int $amount = 1): void {
        $this->questtokens += $amount;
    }

    public function subtractQuestTokens(int $amount = 1): void {
        $this->questtokens -= $amount;
    }

    public function getAllQuests(): array {
        $questsJson = $this->quests;
        return json_decode($questsJson, true) ?? [];
    }
    
    public function getQuest(string $questName): ?array
    {
        $quests = $this->getAllQuests();
        return $quests[$questName] ?? null;
    }
    
    public function addQuest(string $questName, array $questData): void {
        $quests = $this->getAllQuests();
        $quests[$questName] = $questData;
        $this->quests = json_encode($quests);
        $this->updateDb();
    }
    
    public function removeQuest(string $questName): void {
        $quests = $this->getAllQuests();
        if (isset($quests[$questName])) {
            unset($quests[$questName]);
            $this->quests = json_encode($quests);
            $this->updateDb();
        }
    }
    
    public function completeQuest(string $questName): void
    {
        $quests = $this->getAllQuests();
        if (isset($quests[$questName])) {
            $quests[$questName]['status'] = 'completed'; 
            $this->quests = json_encode($quests);
            $this->updateDb();
        }
    }
    
    
    public function incrementQuestProgress(string $questName, string $requirement, int $amount = 1): void
    {
        $quests = json_decode($this->quests, true); 
        if (isset($quests[$questName])) {
            if (isset($quests[$questName][$requirement])) {
                $quests[$questName][$requirement] += $amount;
                $this->quests = json_encode($quests); 
                $this->updateDb();
            }
        }
    }
    
    
    public function getQuestProgress(string $questName, string $requirement): ?int
    {
        $quests = json_decode($this->quests, true); 
        if (isset($quests[$questName])) {
            return $quests[$questName][$requirement] ?? null;
        }
        return null;
    }
    
    

    /**
     * Update player information in the database
     *
     * @return void 
     */
    private function updateDb(): void
    {

        Loader::getDatabase()->executeChange(Queries::PLAYERS_UPDATE, [
            "uuid" => $this->uuid->toString(),
            "username" => $this->username,
            "balance" => $this->balance,
            "gems" => $this->gems   ,
            "kills" => $this->kills,
            "deaths" => $this->deaths,
            "bounty" => $this->bounty,
            "cooldowns" => $this->cooldowns,
            "title" => $this->title,
            "level" => $this->level,
            "settings" => $this->settings,
            "slotcredits" => $this->slotCredits,
            "questtokens" => $this->questtokens,
            "quests" => $this->quests
        ]);
    }

}