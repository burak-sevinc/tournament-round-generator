<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator;

class TournamentMatch
{
    private int $matchId;

    private int $roundId;

    private int $positionInRound;

    private ?int $nextMatchId = null;

    /** @var list<int> */
    private array $prevMatchIds = [];

    private ?Team $teamSlot1 = null;

    private ?Team $teamSlot2 = null;

    private ?string $url = null;

    public function __construct(int $matchId, int $roundId, int $positionInRound)
    {
        $this->matchId = $matchId;
        $this->roundId = $roundId;
        $this->positionInRound = $positionInRound;
    }

    public function getMatchId(): int
    {
        return $this->matchId;
    }

    public function getRoundId(): int
    {
        return $this->roundId;
    }

    public function getPositionInRound(): int
    {
        return $this->positionInRound;
    }

    public function getNextMatchId(): ?int
    {
        return $this->nextMatchId;
    }

    public function setNextMatchId(?int $nextMatchId): self
    {
        $this->nextMatchId = $nextMatchId;

        return $this;
    }

    /** @return list<int> */
    public function getPrevMatchIds(): array
    {
        return $this->prevMatchIds;
    }

    /** @param list<int> $prevMatchIds */
    public function setPrevMatchIds(array $prevMatchIds): self
    {
        $this->prevMatchIds = $prevMatchIds;

        return $this;
    }

    public function getTeamSlot1(): ?Team
    {
        return $this->teamSlot1;
    }

    public function getTeamSlot2(): ?Team
    {
        return $this->teamSlot2;
    }

    public function setTeamSlot1(?Team $team): self
    {
        $this->teamSlot1 = $team;

        return $this;
    }

    public function setTeamSlot2(?Team $team): self
    {
        $this->teamSlot2 = $team;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getTeamBySlot(int $slot): ?Team
    {
        if ($slot === 1) {
            return $this->teamSlot1;
        }

        if ($slot === 2) {
            return $this->teamSlot2;
        }

        return null;
    }

    public function hasTeams(): bool
    {
        return $this->teamSlot1 !== null || $this->teamSlot2 !== null;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'matchId' => $this->matchId,
            'roundId' => $this->roundId,
            'positionInRound' => $this->positionInRound,
            'prevMatchIds' => $this->prevMatchIds,
            'teams' => [
                ['slot' => 1, 'team' => $this->teamSlot1 !== null ? $this->teamSlot1->toArray() : null],
                ['slot' => 2, 'team' => $this->teamSlot2 !== null ? $this->teamSlot2->toArray() : null],
            ],
        ];

        if ($this->nextMatchId !== null) {
            $data['nextMatchId'] = $this->nextMatchId;
        }

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        return $data;
    }
}
