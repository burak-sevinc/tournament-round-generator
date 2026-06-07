<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Support;

use BurakSevinc\TournamentRoundGenerator\TournamentMatch;
use BurakSevinc\TournamentRoundGenerator\Team;

class BracketSeeder
{
    /**
     * @param list<TournamentMatch> $firstRoundMatches
     * @param list<Team> $teams
     */
    public function seedFirstRound(array $firstRoundMatches, array $teams): void
    {
        $seedOrder = $this->buildSeedOrder(count($teams));

        foreach ($firstRoundMatches as $index => $match) {
            $teamIndex1 = $seedOrder[$index * 2];
            $teamIndex2 = $seedOrder[$index * 2 + 1];

            $match->setTeamSlot1($teams[$teamIndex1]);
            $match->setTeamSlot2($teams[$teamIndex2]);
        }
    }

    /** @return list<int> */
    private function buildSeedOrder(int $teamCount): array
    {
        if ($teamCount === 2) {
            return [0, 1];
        }

        $half = $teamCount / 2;
        $previousOrder = $this->buildSeedOrder($half);
        $order = [];

        foreach ($previousOrder as $seedIndex) {
            $order[] = $seedIndex;
            $order[] = $teamCount - 1 - $seedIndex;
        }

        return $order;
    }
}
