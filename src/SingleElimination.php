<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator;

use function log;

class SingleElimination
{
    private int $roundCount;
    private array $rounds  = [];
    private array $matches = [];

    public function __construct(protected int $teamCount)
    {
        $this->roundCount = (int) log($this->teamCount, 2);
        $this->createRoundsAndMatches();
    }

    public function getTeamCount(): int
    {
        return $this->teamCount;
    }

    public function getRoundCount(): int
    {
        return $this->roundCount;
    }

    private function createRoundsAndMatches(): void
    {
        $teamCount = $this->getTeamCount() / 2;
        $matchId = 1;
        for ($i = 1; $i <= $this->roundCount; $i++) {
            for ($j = 1; $j <= $teamCount; $j++) {
                $match = [
                    'roundId' => $i,
                    'matchId' => $matchId
                ];

                $this->rounds[$i][] = $match;
                $this->matches[] = $match;

                $matchId++;
            }

            $teamCount /= 2;
        }
    }

    public function getRounds(): array
    {
        return $this->rounds;
    }

    public function getMatchesByRoundId(int $roundId): array
    {
        return $this->rounds[$roundId];
    }

    public function getMatches(): array
    {
        return $this->matches;
    }

    public function addNextMatchId(): array
    {
        $roundCount = $this->getRoundCount();
        for ($i = 1; $i <= $roundCount; $i++) {
            $matchCount = count($this->rounds[$i]);
            for ($j = 0; $j < $matchCount; $j++) {
                $this->rounds[$i][$j]['nextMatchId'] = $this->rounds[$i + 1][floor($j / 2)]['matchId'] ?? null;
            }
        }

        return $this->rounds;
    }
}
