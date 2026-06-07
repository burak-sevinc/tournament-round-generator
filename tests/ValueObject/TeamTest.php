<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\ValueObject;

use BurakSevinc\TournamentRoundGenerator\Member;
use BurakSevinc\TournamentRoundGenerator\Team;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies basic behavior of the Team value object.
 */
#[Group('value-object')]
final class TeamTest extends TestCase
{
    #[Test]
    #[TestDox('Exposes id, name, seed, and member list')]
    public function it_exposes_core_fields(): void
    {
        $team = new Team(
            id: 1,
            name: 'Alpha',
            seed: 1,
            members: [new Member(10, 'Player A')],
        );

        $this->assertSame(1, $team->getId());
        $this->assertSame('Alpha', $team->getName());
        $this->assertSame(1, $team->getSeed());
        $this->assertCount(1, $team->getMembers());
    }

    #[Test]
    #[TestDox('addMember appends a new member to the existing list')]
    public function it_adds_members_dynamically(): void
    {
        $team = new Team(1, 'Alpha');
        $team->addMember(new Member(1, 'First'));
        $team->addMember(new Member(2, 'Second'));

        $this->assertCount(2, $team->getMembers());
        $this->assertSame('Second', $team->getMembers()[1]->getName());
    }

    #[Test]
    #[TestDox('Allows seed to be optional and null')]
    public function it_allows_null_seed(): void
    {
        $team = new Team(1, 'Unseeded');

        $this->assertNull($team->getSeed());
    }

    #[Test]
    #[TestDox('Serializes nested members via toArray')]
    public function it_serializes_members_in_to_array(): void
    {
        $team = new Team(1, 'Alpha', 1, [new Member(10, 'Player A')]);

        $this->assertSame([
            'id' => 1,
            'name' => 'Alpha',
            'seed' => 1,
            'members' => [
                ['id' => 10, 'name' => 'Player A', 'metadata' => []],
            ],
        ], $team->toArray());
    }
}
