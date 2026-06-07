<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\ValueObject;

use BurakSevinc\TournamentRoundGenerator\Team;
use BurakSevinc\TournamentRoundGenerator\TournamentMatch;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies basic behavior of the TournamentMatch value object.
 */
#[Group('value-object')]
final class TournamentMatchTest extends TestCase
{
    #[Test]
    #[TestDox('Exposes core match identity fields')]
    public function it_exposes_match_identity_fields(): void
    {
        $match = new TournamentMatch(matchId: 5, roundId: 2, positionInRound: 1);

        $this->assertSame(5, $match->getMatchId());
        $this->assertSame(2, $match->getRoundId());
        $this->assertSame(1, $match->getPositionInRound());
    }

    #[Test]
    #[TestDox('Starts with empty links, url, and team slots')]
    public function it_starts_with_empty_links_and_slots(): void
    {
        $match = new TournamentMatch(1, 1, 0);

        $this->assertNull($match->getNextMatchId());
        $this->assertSame([], $match->getPrevMatchIds());
        $this->assertNull($match->getUrl());
        $this->assertFalse($match->hasTeams());
    }

    #[Test]
    #[TestDox('getTeamBySlot returns the team assigned to the requested slot')]
    public function it_returns_team_by_slot_number(): void
    {
        $match = new TournamentMatch(1, 1, 0);
        $team1 = new Team(1, 'Home');
        $team2 = new Team(2, 'Away');

        $match->setTeamSlot1($team1)->setTeamSlot2($team2);

        $this->assertSame('Home', $match->getTeamBySlot(1)?->getName());
        $this->assertSame('Away', $match->getTeamBySlot(2)?->getName());
        $this->assertNull($match->getTeamBySlot(3));
        $this->assertTrue($match->hasTeams());
    }

    #[Test]
    #[TestDox('Omits optional link fields from toArray when they are not set')]
    public function it_omits_optional_fields_from_array_when_not_set(): void
    {
        $match = new TournamentMatch(1, 1, 0);

        $this->assertArrayNotHasKey('nextMatchId', $match->toArray());
        $this->assertArrayNotHasKey('url', $match->toArray());
    }

    #[Test]
    #[TestDox('Includes nextMatchId and url in toArray when they are set')]
    public function it_includes_optional_fields_in_array_when_set(): void
    {
        $match = (new TournamentMatch(1, 1, 0))
            ->setNextMatchId(2)
            ->setPrevMatchIds([3, 4])
            ->setUrl('/match/1');

        $array = $match->toArray();

        $this->assertSame(2, $array['nextMatchId']);
        $this->assertSame('/match/1', $array['url']);
        $this->assertSame([3, 4], $array['prevMatchIds']);
    }
}
