<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\Support;

use BurakSevinc\TournamentRoundGenerator\Member;
use BurakSevinc\TournamentRoundGenerator\Team;

trait CreatesBracketFixtures
{
    /**
     * Creates a list of teams ordered by seed.
     * Each team includes one member with metadata for testability.
     *
     * @return list<Team>
     */
    protected function createTeams(int $count, string $prefix = 'Team'): array
    {
        $teams = [];

        for ($seed = 1; $seed <= $count; $seed++) {
            $teams[] = new Team(
                id: $seed,
                name: sprintf('%s %d', $prefix, $seed),
                seed: $seed,
                members: [
                    new Member(
                        id: $seed * 10,
                        name: sprintf('Player %d', $seed),
                        metadata: ['role' => 'captain'],
                    ),
                ],
            );
        }

        return $teams;
    }

    /**
     * Builds the expected array shape for an unlinked match.
     *
     * @return array<string, mixed>
     */
    protected function expectedUnlinkedMatchArray(int $matchId, int $roundId, int $positionInRound): array
    {
        return [
            'matchId' => $matchId,
            'roundId' => $roundId,
            'positionInRound' => $positionInRound,
            'prevMatchIds' => [],
            'teams' => [
                ['slot' => 1, 'team' => null],
                ['slot' => 2, 'team' => null],
            ],
        ];
    }
}
