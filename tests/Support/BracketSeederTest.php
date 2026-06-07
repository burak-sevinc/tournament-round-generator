<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\Support;

use BurakSevinc\TournamentRoundGenerator\Support\BracketSeeder;
use BurakSevinc\TournamentRoundGenerator\Team;
use BurakSevinc\TournamentRoundGenerator\TournamentMatch;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies classic tournament seed placement logic in BracketSeeder.
 */
#[Group('support')]
#[Group('seeding')]
final class BracketSeederTest extends TestCase
{
    #[Test]
    #[TestDox('Pairs top and bottom seeds for four teams: 1v4, 2v3')]
    public function it_pairs_top_and_bottom_seeds_for_four_teams(): void
    {
        $teams = [
            new Team(1, 'Seed 1', 1),
            new Team(2, 'Seed 2', 2),
            new Team(3, 'Seed 3', 3),
            new Team(4, 'Seed 4', 4),
        ];

        $matches = [
            new TournamentMatch(1, 1, 0),
            new TournamentMatch(2, 1, 1),
        ];

        (new BracketSeeder())->seedFirstRound($matches, $teams);

        $this->assertSame('Seed 1', $matches[0]->getTeamSlot1()?->getName());
        $this->assertSame('Seed 4', $matches[0]->getTeamSlot2()?->getName());
        $this->assertSame('Seed 2', $matches[1]->getTeamSlot1()?->getName());
        $this->assertSame('Seed 3', $matches[1]->getTeamSlot2()?->getName());
    }

    #[Test]
    #[TestDox('Applies standard eight-team bracket pairings: 1v8, 4v5, 2v7, 3v6')]
    public function it_applies_standard_eight_team_bracket_pairings(): void
    {
        $teams = array_map(
            static fn (int $seed): Team => new Team($seed, "Seed $seed", $seed),
            range(1, 8),
        );

        $matches = array_map(
            static fn (int $index): TournamentMatch => new TournamentMatch($index + 1, 1, $index),
            range(0, 3),
        );

        (new BracketSeeder())->seedFirstRound($matches, $teams);

        $expectedPairings = [
            ['Seed 1', 'Seed 8'],
            ['Seed 4', 'Seed 5'],
            ['Seed 2', 'Seed 7'],
            ['Seed 3', 'Seed 6'],
        ];

        foreach ($expectedPairings as $index => [$top, $bottom]) {
            $this->assertSame($top, $matches[$index]->getTeamSlot1()?->getName());
            $this->assertSame($bottom, $matches[$index]->getTeamSlot2()?->getName());
        }
    }

    #[Test]
    #[TestDox('Seeds a two-team bracket as a single 1v2 match')]
    public function it_seeds_a_two_team_final(): void
    {
        $teams = [
            new Team(1, 'Seed 1', 1),
            new Team(2, 'Seed 2', 2),
        ];

        $matches = [new TournamentMatch(1, 1, 0)];

        (new BracketSeeder())->seedFirstRound($matches, $teams);

        $this->assertSame('Seed 1', $matches[0]->getTeamSlot1()?->getName());
        $this->assertSame('Seed 2', $matches[0]->getTeamSlot2()?->getName());
    }
}
