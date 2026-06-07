<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\SingleElimination;

use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies next/prev links after linkMatches() for frontend bracket rendering.
 */
#[Group('single-elimination')]
#[Group('linking')]
final class MatchLinkingTest extends TestCase
{
    #[Test]
    #[TestDox('Links first-round matches forward along the winner path to the next round')]
    public function it_links_first_round_matches_to_their_semifinals(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();

        // Match 1 and 2 → semifinal 5; match 3 and 4 → semifinal 6
        $this->assertSame(5, $bracket->getMatchById(1)?->getNextMatchId());
        $this->assertSame(5, $bracket->getMatchById(2)?->getNextMatchId());
        $this->assertSame(6, $bracket->getMatchById(3)?->getNextMatchId());
        $this->assertSame(6, $bracket->getMatchById(4)?->getNextMatchId());
    }

    #[Test]
    #[TestDox('Links semifinals to the final and sets the final nextMatchId to null')]
    public function it_links_semifinals_to_final_and_marks_final_as_terminal(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();

        $this->assertSame(7, $bracket->getMatchById(5)?->getNextMatchId());
        $this->assertSame(7, $bracket->getMatchById(6)?->getNextMatchId());
        $this->assertNull($bracket->getMatchById(7)?->getNextMatchId());
    }

    #[Test]
    #[TestDox('Leaves first-round prevMatchIds empty because there are no feeder matches')]
    public function it_leaves_first_round_matches_without_feeders(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();

        foreach ([1, 2, 3, 4] as $matchId) {
            $this->assertSame([], $bracket->getMatchById($matchId)?->getPrevMatchIds());
        }
    }

    #[Test]
    #[TestDox('Sets prevMatchIds on later-round matches to their two feeder match ids')]
    public function it_sets_prev_match_ids_for_later_rounds(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();

        $this->assertSame([1, 2], $bracket->getMatchById(5)?->getPrevMatchIds());
        $this->assertSame([3, 4], $bracket->getMatchById(6)?->getPrevMatchIds());
        $this->assertSame([5, 6], $bracket->getMatchById(7)?->getPrevMatchIds());
    }

    #[Test]
    #[TestDox('Keeps the flat getMatches list in sync with getRounds after linkMatches')]
    public function it_keeps_flat_match_list_in_sync_with_rounds(): void
    {
        $bracket = (new SingleElimination(8))->linkMatches();

        foreach ($bracket->getMatches() as $matchArray) {
            $match = $bracket->getMatchById($matchArray['matchId']);

            $this->assertNotNull($match, sprintf('Match #%d was not found', $matchArray['matchId']));
            $this->assertSame($matchArray['nextMatchId'] ?? null, $match->getNextMatchId());
            $this->assertSame($matchArray['prevMatchIds'], $match->getPrevMatchIds());
        }
    }

    #[Test]
    #[TestDox('Links a two-team bracket as a single-match final')]
    public function it_links_a_two_team_bracket_as_a_single_final(): void
    {
        $bracket = (new SingleElimination(2))->linkMatches();
        $final = $bracket->getMatchById(1);

        $this->assertNotNull($final);
        $this->assertNull($final->getNextMatchId());
        $this->assertSame([], $final->getPrevMatchIds());
    }

    #[Test]
    #[TestDox('Supports deprecated addNextMatchId as a backward-compatible alias for linkMatches')]
    public function it_supports_deprecated_add_next_match_id_alias(): void
    {
        $rounds = (new SingleElimination(8))->addNextMatchId();

        $this->assertSame(5, $rounds[1][0]['nextMatchId']);
        $this->assertSame([1, 2], $rounds[2][0]['prevMatchIds']);
    }
}
