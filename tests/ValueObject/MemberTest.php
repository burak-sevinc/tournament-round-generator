<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\ValueObject;

use BurakSevinc\TournamentRoundGenerator\Member;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies basic behavior of the Member value object.
 */
#[Group('value-object')]
final class MemberTest extends TestCase
{
    #[Test]
    #[TestDox('Exposes id, name, and metadata fields')]
    public function it_exposes_id_name_and_metadata(): void
    {
        $member = new Member(
            id: 42,
            name: 'Alice',
            metadata: ['country' => 'TR'],
        );

        $this->assertSame(42, $member->getId());
        $this->assertSame('Alice', $member->getName());
        $this->assertSame(['country' => 'TR'], $member->getMetadata());
    }

    #[Test]
    #[TestDox('Supports string ids')]
    public function it_supports_string_ids(): void
    {
        $member = new Member('player-uuid', 'Bob');

        $this->assertSame('player-uuid', $member->getId());
    }

    #[Test]
    #[TestDox('Serializes all fields via toArray')]
    public function it_serializes_to_array(): void
    {
        $member = new Member(1, 'Captain', ['role' => 'captain']);

        $this->assertSame([
            'id' => 1,
            'name' => 'Captain',
            'metadata' => ['role' => 'captain'],
        ], $member->toArray());
    }
}
