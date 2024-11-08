<?php

declare(strict_types=1);

namespace xtcy\ElysiumCore\player;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use xtcy\ElysiumCore\Loader;
use xtcy\ElysiumCore\utils\Queries;

final class PlayerManager
{

    use SingletonTrait;

    /** @var ElysiumPlayer[] */
    private array $sessions; // array to fetch player data

    public function __construct(
        public Loader $plugin
    ){
        self::setInstance($this);

        $this->loadSessions();
    }

    /**
     * Store all player data in $sessions property
     *
     * @return void
     */
    private function loadSessions(): void
    {
        Loader::getDatabase()->executeSelect(Queries::PLAYERS_SELECT, [], function (array $rows): void {
            foreach ($rows as $row) {
                $this->sessions[$row["uuid"]] = new ElysiumPlayer(
                    Uuid::fromString($row["uuid"]),
                    $row["username"],
                    $row["balance"],
                    $row["gems"],
                    $row["kills"],
                    $row["deaths"],
                    $row["bounty"],
                    $row["cooldowns"],
                    $row["title"],
                    $row["level"],
                    $row["settings"],
                    $row["slotcredits"],
                    $row["questtokens"],
                    $row["quests"]
                );
            }
        });
    }

    /**
     * Create a session
     *
     * @param Player $player
     * @return ElysiumPlayer
     * @throws \JsonException
     */
    public function createSession(Player $player): ElysiumPlayer
    {
        $args = [
            "uuid" => $player->getUniqueId()->toString(),
            "username" => $player->getName(),
            "balance" => 10000,
            "gems" => 0,
            "kills" => 0,
            "deaths" => 0,
            "bounty" => 0,
            "cooldowns" => "{}",
            "title" => "",
            "level" => 0,
            "settings" => "{}",
            "slotcredits" => 0,
            "questtokens" => 0,
            "quests" => "{}"
        ];

        Loader::getDatabase()->executeInsert(Queries::PLAYERS_CREATE, $args);

        $this->sessions[$player->getUniqueId()->toString()] = new ElysiumPlayer(
            $player->getUniqueId(),
            $args["username"],
            $args["balance"],
            $args["gems"],
            $args["kills"],
            $args["deaths"],
            $args["bounty"],
            $args["cooldowns"],
            $args["title"],
            $args["level"],
            $args["settings"],
            $args["slotcredits"],
            $args["questtokens"],
            $args["quests"]
        );
        return $this->sessions[$player->getUniqueId()->toString()];
    }

    /**
     * Get session by player object
     *
     * @param Player $player
     * @return ElysiumPlayer|null
     */
    public function getSession(Player $player) : ?ElysiumPlayer
    {
        return $this->getSessionByUuid($player->getUniqueId());
    }

    /**
     * Get session by player name
     *
     * @param string $name
     * @return ElysiumPlayer|null
     */
    public function getSessionByName(string $name) : ?ElysiumPlayer
    {
        foreach ($this->sessions as $session) {
            if (strtolower($session->getUsername()) === strtolower($name)) {
                return $session;
            }
        }
        return null;
    }

    /**
     * Get session by UuidInterface
     *
     * @param UuidInterface $uuid
     * @return ElysiumPlayer|null
     */
    public function getSessionByUuid(UuidInterface $uuid) : ?ElysiumPlayer
    {
        return $this->sessions[$uuid->toString()] ?? null;
    }

    public function destroySession(ElysiumPlayer $session) : void
    {
        Loader::getDatabase()->executeChange(Queries::PLAYERS_DELETE, ["uuid", $session->getUuid()->toString()]);

        # Remove session from the array
        unset($this->sessions[$session->getUuid()->toString()]);
    }

    public function getSessions() : array
    {
        return $this->sessions;
    }

}