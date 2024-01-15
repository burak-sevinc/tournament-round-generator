<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests;

use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use PHPUnit\Framework\TestCase;

class SingleEliminationTest extends TestCase
{
    /** @test  */
    public function shouldGetTeamCountSuccessfully(): void
    {
        $teamCount         = 8;
        $singleElimination = new SingleElimination($teamCount);

        $this->assertSame($teamCount, $singleElimination->getTeamCount());
    }

    /** @test  */
    public function shouldGetRoundCountSuccessfully(): void
    {
        $teamCount         = 8;
        $singleElimination = new SingleElimination($teamCount);

        // log2(8) = 3, so there should be 3 rounds
        $this->assertSame(3, $singleElimination->getRoundCount());
    }

    /** @test  */
    public function shouldGetRoundsReturnSuccessfully(): void
    {
        // Arrange
        $teamCount         = 8;
        $singleElimination = new SingleElimination($teamCount);


        // Act
        $expectedRounds = [
            1 => [
                [
                    'roundId' => 1,
                    'matchId' => 1,
                ],
                [
                    'roundId' => 1,
                    'matchId' => 2,
                ],
                [
                    'roundId' => 1,
                    'matchId' => 3,
                ],
                [
                    'roundId' => 1,
                    'matchId' => 4,
                ],
            ],
            2 => [
                [
                    'roundId' => 2,
                    'matchId' => 5,
                ],
                [
                    'roundId' => 2,
                    'matchId' => 6,
                ],
            ],

            3 => [
                [
                    'roundId' => 3,
                    'matchId' => 7,
                ],
            ],

        ];

        // Assert
        $this->assertSame($expectedRounds, $singleElimination->getRounds());
    }
}
