<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\SingleElimination;

use BurakSevinc\TournamentRoundGenerator\Exception\InvalidTeamCountException;
use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use BurakSevinc\TournamentRoundGenerator\Team;
use BurakSevinc\TournamentRoundGenerator\Tests\Support\CreatesBracketFixtures;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies team seeding and the fromTeams / setTeams flows.
 */
#[Group('single-elimination')]
#[Group('seeding')]
final class TeamSeedingTest extends TestCase
{
    use CreatesBracketFixtures;

    #[Test]
    #[TestDox('fromTeams builds a bracket and applies classic first-round seed placement')]
    public function it_seeds_teams_via_from_teams_factory(): void
    {
        $bracket = SingleElimination::fromTeams($this->createTeams(8));

        // Classic bracket: 1v8, 4v5, 2v7, 3v6
        $this->assertSame('Team 1', $bracket->getTeamsInMatch(1)[0]?->getName());
        $this->assertSame('Team 8', $bracket->getTeamsInMatch(1)[1]?->getName());
        $this->assertSame('Team 4', $bracket->getTeamsInMatch(2)[0]?->getName());
        $this->assertSame('Team 5', $bracket->getTeamsInMatch(2)[1]?->getName());
        $this->assertSame('Team 2', $bracket->getTeamsInMatch(3)[0]?->getName());
        $this->assertSame('Team 7', $bracket->getTeamsInMatch(3)[1]?->getName());
        $this->assertSame('Team 3', $bracket->getTeamsInMatch(4)[0]?->getName());
        $this->assertSame('Team 6', $bracket->getTeamsInMatch(4)[1]?->getName());
    }

    #[Test]
    #[TestDox('setTeams can assign teams to an existing bracket skeleton later')]
    public function it_allows_late_team_assignment_via_set_teams(): void
    {
        $teams = $this->createTeams(8);
        $bracket = (new SingleElimination(8))->setTeams($teams);

        $this->assertSame('Team 1', $bracket->getTeamById(1)?->getName());
        $this->assertSame('Player 1', $bracket->getMembersByTeamId(1)[0]->getName());
    }

    #[Test]
    #[TestDox('Leaves team slots empty in later rounds until winners are provided externally')]
    public function it_leaves_later_round_team_slots_empty_until_results_are_known(): void
    {
        $bracket = SingleElimination::fromTeams($this->createTeams(8));

        foreach ([5, 6, 7] as $matchId) {
            [$slot1, $slot2] = $bracket->getTeamsInMatch($matchId);

            $this->assertNull($slot1, sprintf('Match #%d slot 1 should be empty', $matchId));
            $this->assertNull($slot2, sprintf('Match #%d slot 2 should be empty', $matchId));
        }
    }

    #[Test]
    #[TestDox('Rejects two teams that share the same seed value')]
    public function it_rejects_duplicate_seed_values(): void
    {
        $teams = $this->createTeams(4);
        $teams[1] = new Team(99, 'Duplicate Seed', 1);

        $this->expectException(InvalidTeamCountException::class);
        $this->expectExceptionMessage('Duplicate seed value: 1');

        (new SingleElimination(4))->setTeams($teams);
    }

    #[Test]
    #[TestDox('Serializes seeded first-round matches with team and member data in slot structure')]
    public function it_serializes_seeded_first_round_matches_with_team_data(): void
    {
        $bracket = SingleElimination::fromTeams($this->createTeams(2));
        $match = $bracket->getMatchesByRoundId(1)[0];

        $this->assertSame('Team 1', $match['teams'][0]['team']['name']);
        $this->assertSame('Team 2', $match['teams'][1]['team']['name']);
        $this->assertSame('Player 1', $match['teams'][0]['team']['members'][0]['name']);
        $this->assertSame('captain', $match['teams'][0]['team']['members'][0]['metadata']['role']);
    }
}
