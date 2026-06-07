<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\SingleElimination;

use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use BurakSevinc\TournamentRoundGenerator\Team;
use BurakSevinc\TournamentRoundGenerator\Tests\Support\CreatesBracketFixtures;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies bracket query APIs for matches, teams, feeders, and next match.
 */
#[Group('single-elimination')]
#[Group('query')]
final class QueryApiTest extends TestCase
{
    use CreatesBracketFixtures;

    #[Test]
    #[TestDox('getMatchById returns an existing match as a TournamentMatch instance')]
    public function it_finds_existing_match_by_id(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();
        $match = $bracket->getMatchById(5);

        $this->assertNotNull($match);
        $this->assertSame(5, $match->getMatchId());
        $this->assertSame(2, $match->getRoundId());
    }

    #[Test]
    #[TestDox('getMatchById returns null for an unknown match id')]
    public function it_returns_null_for_unknown_match_id(): void
    {
        $bracket = new SingleElimination(8);

        $this->assertNull($bracket->getMatchById(999));
    }

    #[Test]
    #[TestDox('getFeederMatches returns the previous matches that feed into a given match')]
    public function it_returns_feeder_matches_for_a_given_match(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();
        $feeders = $bracket->getFeederMatches(5);

        $this->assertCount(2, $feeders);
        $this->assertSame(1, $feeders[0]->getMatchId());
        $this->assertSame(2, $feeders[1]->getMatchId());
    }

    #[Test]
    #[TestDox('getFeederMatches returns an empty list for first-round matches')]
    public function it_returns_empty_feeders_for_first_round_matches(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();

        $this->assertSame([], $bracket->getFeederMatches(1));
    }

    #[Test]
    #[TestDox('getNextMatch returns the next match along the winner path')]
    public function it_returns_next_match_in_winner_path(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();
        $nextMatch = $bracket->getNextMatch(5);

        $this->assertNotNull($nextMatch);
        $this->assertSame(7, $nextMatch->getMatchId());
    }

    #[Test]
    #[TestDox('getNextMatch returns null for the final match')]
    public function it_returns_null_next_match_for_final(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();

        $this->assertNull($bracket->getNextMatch(7));
    }

    #[Test]
    #[TestDox('getTeamById can resolve teams with string ids')]
    public function it_finds_teams_by_string_id(): void
    {
        $teams = [
            new Team('alpha', 'Alpha Squad', 1),
            new Team('beta', 'Beta Squad', 2),
        ];
        $bracket = SingleElimination::fromTeams($teams);

        $this->assertSame('Alpha Squad', $bracket->getTeamById('alpha')?->getName());
        $this->assertNull($bracket->getTeamById('unknown'));
    }

    #[Test]
    #[TestDox('getMembersByTeamId returns an empty array for an unknown team')]
    public function it_returns_empty_members_for_unknown_team(): void
    {
        $bracket = SingleElimination::fromTeams($this->createTeams(2));

        $this->assertSame([], $bracket->getMembersByTeamId(999));
    }

    #[Test]
    #[TestDox('getTeamsInMatch returns two null slots for an unknown match')]
    public function it_returns_null_slots_for_unknown_match(): void
    {
        $bracket = new SingleElimination(8);

        $this->assertSame([null, null], $bracket->getTeamsInMatch(999));
    }

    #[Test]
    #[TestDox('Returns empty feeder and next match results for an unknown match id')]
    public function it_returns_empty_results_for_unknown_match_in_feeder_and_next_queries(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();

        $this->assertSame([], $bracket->getFeederMatches(999));
        $this->assertNull($bracket->getNextMatch(999));
    }
}
