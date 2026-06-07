<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\SingleElimination;

use BurakSevinc\TournamentRoundGenerator\Contract\MatchUrlGenerator;
use BurakSevinc\TournamentRoundGenerator\Exception\InvalidTeamCountException;
use BurakSevinc\TournamentRoundGenerator\Member;
use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use BurakSevinc\TournamentRoundGenerator\Support\TemplateMatchUrlGenerator;
use BurakSevinc\TournamentRoundGenerator\Team;
use BurakSevinc\TournamentRoundGenerator\Tests\Support\CreatesBracketFixtures;
use BurakSevinc\TournamentRoundGenerator\TournamentMatch;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end workflows that mirror how a developer integrates the library
 * to create and expose a single-elimination tournament.
 */
#[Group('single-elimination')]
#[Group('workflow')]
final class DeveloperWorkflowTest extends TestCase
{
    use CreatesBracketFixtures;

    #[Test]
    #[TestDox('Builds a bracket skeleton from team count before teams are assigned')]
    public function it_builds_a_skeleton_bracket_from_team_count_only(): void
    {
        $bracket = new SingleElimination(8);

        $this->assertSame(8, $bracket->getTeamCount());
        $this->assertSame(3, $bracket->getRoundCount());
        $this->assertCount(7, $bracket->getMatches());
        $this->assertCount(4, $bracket->getMatchesByRoundId(1));

        foreach ($bracket->getMatches() as $match) {
            $this->assertNull($match['teams'][0]['team']);
            $this->assertNull($match['teams'][1]['team']);
        }
    }

    #[Test]
    #[TestDox('Creates a complete tournament from teams with linking and match URLs')]
    public function it_creates_a_full_tournament_from_teams_with_linking_and_urls(): void
    {
        $bracket = SingleElimination::fromTeams($this->createTeams(8))
            ->linkMatches()
            ->setMatchUrlGenerator(new TemplateMatchUrlGenerator('/tournament/match/{matchId}'))
            ->applyMatchUrls();

        $this->assertSame('Team 1', $bracket->getTeamById(1)?->getName());
        $this->assertSame('Player 1', $bracket->getMembersByTeamId(1)[0]->getName());
        $this->assertSame(7, $bracket->getNextMatch(5)?->getMatchId());
        $this->assertSame('/tournament/match/1', $bracket->getMatchById(1)?->getUrl());
    }

    #[Test]
    #[TestDox('Supports assigning teams to an existing skeleton before linking')]
    public function it_supports_deferred_team_assignment_on_existing_skeleton(): void
    {
        $bracket = (new SingleElimination(4))
            ->setTeams($this->createTeams(4))
            ->linkMatches();

        $this->assertSame('Team 1', $bracket->getTeamsInMatch(1)[0]?->getName());
        $this->assertSame([1, 2], $bracket->getMatchById(3)?->getPrevMatchIds());
    }

    #[Test]
    #[TestDox('Produces frontend-ready match payloads with linking and layout metadata')]
    public function it_produces_frontend_ready_match_payloads(): void
    {
        $bracket = SingleElimination::fromTeams($this->createTeams(8))->linkMatches();
        $semifinal = $bracket->getMatchesByRoundId(2)[0];

        $this->assertArrayHasKey('matchId', $semifinal);
        $this->assertArrayHasKey('roundId', $semifinal);
        $this->assertArrayHasKey('positionInRound', $semifinal);
        $this->assertArrayHasKey('prevMatchIds', $semifinal);
        $this->assertArrayHasKey('nextMatchId', $semifinal);
        $this->assertArrayHasKey('teams', $semifinal);
        $this->assertSame([1, 2], $semifinal['prevMatchIds']);
        $this->assertSame(7, $semifinal['nextMatchId']);
    }

    #[Test]
    #[TestDox('Serializes bracket data to JSON without error for API responses')]
    public function it_serializes_bracket_data_to_json_without_error(): void
    {
        $bracket = SingleElimination::fromTeams($this->createTeams(4))
            ->linkMatches()
            ->setMatchUrlGenerator(new TemplateMatchUrlGenerator('/match/{matchId}'))
            ->applyMatchUrls();

        $payload = [
            'teamCount' => $bracket->getTeamCount(),
            'roundCount' => $bracket->getRoundCount(),
            'rounds' => $bracket->getRounds(),
            'matches' => $bracket->getMatches(),
        ];

        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->assertIsString($json);
        $this->assertStringContainsString('"matchId":1', $json);
    }

    #[Test]
    #[TestDox('Supports custom MatchUrlGenerator implementations')]
    public function it_supports_custom_match_url_generators(): void
    {
        $generator = new class implements MatchUrlGenerator {
            public function generate(TournamentMatch $match): string
            {
                return sprintf('/api/v1/matches/%d?round=%d', $match->getMatchId(), $match->getRoundId());
            }
        };

        $bracket = (new SingleElimination(2))
            ->setMatchUrlGenerator($generator)
            ->applyMatchUrls();

        $this->assertSame('/api/v1/matches/1?round=1', $bracket->getMatchById(1)?->getUrl());
    }

    #[Test]
    #[TestDox('Allows teams without seed values when assigning to a bracket')]
    public function it_allows_teams_without_seed_values(): void
    {
        $teams = [
            new Team(1, 'Alpha', null, [new Member(1, 'Player A')]),
            new Team(2, 'Beta', null, [new Member(2, 'Player B')]),
        ];

        $bracket = SingleElimination::fromTeams($teams);

        $this->assertSame('Alpha', $bracket->getTeamsInMatch(1)[0]?->getName());
        $this->assertSame('Beta', $bracket->getTeamsInMatch(1)[1]?->getName());
    }

    #[Test]
    #[TestDox('Rejects fromTeams when the team count is not a power of two')]
    public function it_rejects_from_teams_with_invalid_team_count(): void
    {
        $this->expectException(InvalidTeamCountException::class);

        SingleElimination::fromTeams([
            new Team(1, 'One'),
            new Team(2, 'Two'),
            new Team(3, 'Three'),
        ]);
    }

    #[Test]
    #[TestDox('Exposes every first-round team through match and team lookup APIs')]
    public function it_exposes_all_first_round_teams_through_query_apis(): void
    {
        $bracket = SingleElimination::fromTeams($this->createTeams(4));

        $discoveredTeamIds = [];

        foreach ($bracket->getMatchesByRoundId(1) as $match) {
            foreach ($match['teams'] as $slot) {
                if ($slot['team'] !== null) {
                    $discoveredTeamIds[] = $slot['team']['id'];
                    $this->assertSame($slot['team']['name'], $bracket->getTeamById($slot['team']['id'])?->getName());
                }
            }
        }

        sort($discoveredTeamIds);
        $this->assertSame([1, 2, 3, 4], $discoveredTeamIds);
    }

    #[Test]
    #[TestDox('Keeps method chaining fluent across setup, linking, and URL assignment')]
    public function it_supports_fluent_method_chaining(): void
    {
        $bracket = (new SingleElimination(2))
            ->setTeams($this->createTeams(2))
            ->linkMatches()
            ->setMatchUrlGenerator(new TemplateMatchUrlGenerator('/match/{matchId}'))
            ->applyMatchUrls();

        $this->assertInstanceOf(SingleElimination::class, $bracket);
        $this->assertSame('/match/1', $bracket->getMatchById(1)?->getUrl());
    }
}
