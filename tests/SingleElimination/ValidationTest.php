<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\SingleElimination;

use BurakSevinc\TournamentRoundGenerator\Exception\InvalidTeamCountException;
use BurakSevinc\TournamentRoundGenerator\Exception\TeamCountMismatchException;
use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use BurakSevinc\TournamentRoundGenerator\Tests\Support\CreatesBracketFixtures;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies invalid team counts and mismatched team list scenarios.
 */
#[Group('single-elimination')]
#[Group('validation')]
final class ValidationTest extends TestCase
{
    use CreatesBracketFixtures;

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidTeamCountProvider(): iterable
    {
        yield '0 teams' => [0];
        yield '1 team' => [1];
        yield '3 teams (not a power of 2)' => [3];
        yield '6 teams (not a power of 2)' => [6];
        yield '12 teams (not a power of 2)' => [12];
    }

    #[Test]
    #[DataProvider('invalidTeamCountProvider')]
    #[TestDox('Throws InvalidTeamCountException when team count is not a power of 2')]
    public function it_rejects_non_power_of_two_team_counts(int $teamCount): void
    {
        $this->expectException(InvalidTeamCountException::class);
        $this->expectExceptionMessage(sprintf('Team count must be a power of 2. Got: %d', $teamCount));

        new SingleElimination($teamCount);
    }

    #[Test]
    #[TestDox('Throws when setTeams receives a team count that does not match the bracket size')]
    public function it_rejects_team_count_mismatch_on_set_teams(): void
    {
        $this->expectException(TeamCountMismatchException::class);
        $this->expectExceptionMessage('Expected 8 teams, got 7.');

        (new SingleElimination(8))->setTeams(array_slice($this->createTeams(8), 0, 7));
    }

    #[Test]
    #[TestDox('Accepts valid power-of-two team counts without throwing')]
    public function it_accepts_valid_power_of_two_team_counts(): void
    {
        foreach ([2, 4, 8, 16, 32] as $teamCount) {
            $bracket = new SingleElimination($teamCount);
            $this->assertSame($teamCount, $bracket->getTeamCount());
        }
    }
}
