<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\SingleElimination;

use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use BurakSevinc\TournamentRoundGenerator\Tests\Support\CreatesBracketFixtures;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that the bracket skeleton is built with the correct round and match counts.
 */
#[Group('single-elimination')]
#[Group('structure')]
final class BracketStructureTest extends TestCase
{
    use CreatesBracketFixtures;

    #[Test]
    #[TestDox('Exposes the configured team count from the bracket instance')]
    public function it_exposes_the_configured_team_count(): void
    {
        $bracket = new SingleElimination(8);

        $this->assertSame(8, $bracket->getTeamCount());
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function roundCountProvider(): iterable
    {
        yield '2 teams → 1 round (final)' => [2, 1];
        yield '4 teams → 2 rounds' => [4, 2];
        yield '8 teams → 3 rounds' => [8, 3];
        yield '16 teams → 4 rounds' => [16, 4];
        yield '32 teams → 5 rounds' => [32, 5];
    }

    #[Test]
    #[DataProvider('roundCountProvider')]
    #[TestDox('Calculates round count from team count using the log2 formula')]
    public function it_calculates_round_count_from_team_count(int $teamCount, int $expectedRoundCount): void
    {
        $bracket = new SingleElimination($teamCount);

        $this->assertSame($expectedRoundCount, $bracket->getRoundCount());
    }

    #[Test]
    #[TestDox('Builds all rounds for an eight-team bracket with correct match ids and positions')]
    public function it_builds_all_rounds_for_eight_teams(): void
    {
        $bracket = new SingleElimination(8);

        $this->assertSame([
            1 => [
                $this->expectedUnlinkedMatchArray(1, 1, 0),
                $this->expectedUnlinkedMatchArray(2, 1, 1),
                $this->expectedUnlinkedMatchArray(3, 1, 2),
                $this->expectedUnlinkedMatchArray(4, 1, 3),
            ],
            2 => [
                $this->expectedUnlinkedMatchArray(5, 2, 0),
                $this->expectedUnlinkedMatchArray(6, 2, 1),
            ],
            3 => [
                $this->expectedUnlinkedMatchArray(7, 3, 0),
            ],
        ], $bracket->getRounds());
    }

    #[Test]
    #[TestDox('Total match count is always one less than the team count (n - 1)')]
    public function it_generates_n_minus_one_matches(): void
    {
        foreach ([2, 4, 8, 16] as $teamCount) {
            $bracket = new SingleElimination($teamCount);

            $this->assertCount(
                $teamCount - 1,
                $bracket->getMatches(),
                sprintf('Match count for %d teams', $teamCount),
            );
        }
    }

    #[Test]
    #[TestDox('getMatchesByRoundId returns only matches from the requested round')]
    public function it_returns_matches_for_a_specific_round(): void
    {
        $bracket = new SingleElimination(8);
        $roundOne = $bracket->getMatchesByRoundId(1);

        $this->assertCount(4, $roundOne);
        $this->assertSame([1, 1, 1, 1], array_column($roundOne, 'roundId'));
        $this->assertSame([1, 2, 3, 4], array_column($roundOne, 'matchId'));
    }

    #[Test]
    #[TestDox('positionInRound represents zero-based vertical order within each round')]
    public function it_assigns_zero_based_position_in_round(): void
    {
        $bracket = new SingleElimination(8);

        $this->assertSame([0, 1, 2, 3], array_column($bracket->getMatchesByRoundId(1), 'positionInRound'));
        $this->assertSame([0, 1], array_column($bracket->getMatchesByRoundId(2), 'positionInRound'));
        $this->assertSame([0], array_column($bracket->getMatchesByRoundId(3), 'positionInRound'));
    }

    #[Test]
    #[TestDox('getMatchObjects and getRoundObjects return value object references')]
    public function it_exposes_match_and_round_objects(): void
    {
        $bracket = new SingleElimination(4);

        $this->assertCount(3, $bracket->getMatchObjects());
        $this->assertCount(2, $bracket->getRoundObjects());
        $this->assertSame(1, $bracket->getMatchObjects()[0]->getMatchId());
    }
}
