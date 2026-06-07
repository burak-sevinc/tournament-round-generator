<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator;

class Team
{
    private int|string $id;

    private string $name;

    private ?int $seed;

    /** @var list<Member> */
    private array $members = [];

    /**
     * @param list<Member> $members
     */
    public function __construct(int|string $id, string $name, ?int $seed = null, array $members = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->seed = $seed;

        foreach ($members as $member) {
            $this->addMember($member);
        }
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSeed(): ?int
    {
        return $this->seed;
    }

    /** @return list<Member> */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function addMember(Member $member): self
    {
        $this->members[] = $member;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'seed' => $this->seed,
            'members' => array_map(static fn (Member $member): array => $member->toArray(), $this->members),
        ];
    }
}
