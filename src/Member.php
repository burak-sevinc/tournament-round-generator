<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator;

class Member
{
    private int|string $id;

    private string $name;

    /** @var array<string, mixed> */
    private array $metadata;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(int|string $id, string $name, array $metadata = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->metadata = $metadata;
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return array<string, mixed> */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'metadata' => $this->metadata,
        ];
    }
}
