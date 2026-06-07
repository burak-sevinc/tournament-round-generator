<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator;

use BurakSevinc\TournamentRoundGenerator\Contract\MatchUrlGenerator;
use BurakSevinc\TournamentRoundGenerator\Exception\InvalidTeamCountException;
use BurakSevinc\TournamentRoundGenerator\Exception\TeamCountMismatchException;
use BurakSevinc\TournamentRoundGenerator\Support\BracketSeeder;

use function log;

class SingleElimination
{
    private int $roundCount;

    /** @var array<int, TournamentMatch[]> */
    private array $rounds = [];

    /** @var list<TournamentMatch> */
    private array $matches = [];

    /** @var array<int|string, Team> */
    private array $teamsById = [];

    private ?MatchUrlGenerator $matchUrlGenerator = null;

    public function __construct(protected int $teamCount)
    {
        self::assertValidTeamCount($teamCount);

        $this->roundCount = (int) log($this->teamCount, 2);
        $this->createRoundsAndMatches();
    }

    /**
     * @param list<Team> $teams
     */
    public static function fromTeams(array $teams): self
    {
        $bracket = new self(count($teams));

        return $bracket->setTeams($teams);
    }

    public function getTeamCount(): int
    {
        return $this->teamCount;
    }

    public function getRoundCount(): int
    {
        return $this->roundCount;
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function getRounds(): array
    {
        $rounds = [];

        foreach ($this->rounds as $roundId => $matches) {
            $rounds[$roundId] = array_map(static fn (TournamentMatch $match): array => $match->toArray(), $matches);
        }

        return $rounds;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getMatchesByRoundId(int $roundId): array
    {
        return array_map(static fn (TournamentMatch $match): array => $match->toArray(), $this->rounds[$roundId]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getMatches(): array
    {
        return array_map(static fn (TournamentMatch $match): array => $match->toArray(), $this->matches);
    }

    /**
     * @return list<TournamentMatch>
     */
    public function getMatchObjects(): array
    {
        return $this->matches;
    }

    /**
     * @return array<int, TournamentMatch[]>
     */
    public function getRoundObjects(): array
    {
        return $this->rounds;
    }

    public function getMatchById(int $matchId): ?TournamentMatch
    {
        foreach ($this->matches as $match) {
            if ($match->getMatchId() === $matchId) {
                return $match;
            }
        }

        return null;
    }

    public function getTeamById(int|string $teamId): ?Team
    {
        return $this->teamsById[$teamId] ?? null;
    }

    /**
     * @return array{0: Team|null, 1: Team|null}
     */
    public function getTeamsInMatch(int $matchId): array
    {
        $match = $this->getMatchById($matchId);

        if ($match === null) {
            return [null, null];
        }

        return [$match->getTeamSlot1(), $match->getTeamSlot2()];
    }

    /** @return list<Member> */
    public function getMembersByTeamId(int|string $teamId): array
    {
        $team = $this->getTeamById($teamId);

        if ($team === null) {
            return [];
        }

        return $team->getMembers();
    }

    /**
     * @return list<TournamentMatch>
     */
    public function getFeederMatches(int $matchId): array
    {
        $match = $this->getMatchById($matchId);

        if ($match === null) {
            return [];
        }

        $feeders = [];

        foreach ($match->getPrevMatchIds() as $feederMatchId) {
            $feeder = $this->getMatchById($feederMatchId);

            if ($feeder !== null) {
                $feeders[] = $feeder;
            }
        }

        return $feeders;
    }

    public function getNextMatch(int $matchId): ?TournamentMatch
    {
        $match = $this->getMatchById($matchId);

        if ($match === null || $match->getNextMatchId() === null) {
            return null;
        }

        return $this->getMatchById($match->getNextMatchId());
    }

    /**
     * @param list<Team> $teams
     */
    public function setTeams(array $teams): self
    {
        if (count($teams) !== $this->teamCount) {
            throw TeamCountMismatchException::create($this->teamCount, count($teams));
        }

        $this->validateTeamSeeds($teams);
        $this->indexTeams($teams);

        (new BracketSeeder())->seedFirstRound($this->rounds[1], $teams);

        return $this;
    }

    public function linkMatches(): self
    {
        $roundCount = $this->getRoundCount();

        for ($roundId = 1; $roundId <= $roundCount; $roundId++) {
            $matchCount = count($this->rounds[$roundId]);

            for ($index = 0; $index < $matchCount; $index++) {
                $match = $this->rounds[$roundId][$index];
                $nextRoundId = $roundId + 1;
                $nextMatchIndex = (int) floor($index / 2);

                if (isset($this->rounds[$nextRoundId][$nextMatchIndex])) {
                    $nextMatch = $this->rounds[$nextRoundId][$nextMatchIndex];
                    $match->setNextMatchId($nextMatch->getMatchId());
                } else {
                    $match->setNextMatchId(null);
                }
            }
        }

        for ($roundId = 2; $roundId <= $roundCount; $roundId++) {
            foreach ($this->rounds[$roundId] as $index => $match) {
                $prevMatch1 = $this->rounds[$roundId - 1][$index * 2];
                $prevMatch2 = $this->rounds[$roundId - 1][$index * 2 + 1];

                $match->setPrevMatchIds([
                    $prevMatch1->getMatchId(),
                    $prevMatch2->getMatchId(),
                ]);
            }
        }

        $this->syncMatchesFromRounds();

        return $this;
    }

    /**
     * @deprecated Use linkMatches() instead.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function addNextMatchId(): array
    {
        $this->linkMatches();

        return $this->getRounds();
    }

    public function setMatchUrlGenerator(MatchUrlGenerator $generator): self
    {
        $this->matchUrlGenerator = $generator;

        return $this;
    }

    public function applyMatchUrls(): self
    {
        if ($this->matchUrlGenerator === null) {
            return $this;
        }

        foreach ($this->matches as $match) {
            $match->setUrl($this->matchUrlGenerator->generate($match));
        }

        return $this;
    }

    private function createRoundsAndMatches(): void
    {
        $matchesInRound = $this->getTeamCount() / 2;
        $matchId = 1;

        for ($roundId = 1; $roundId <= $this->roundCount; $roundId++) {
            for ($positionInRound = 0; $positionInRound < $matchesInRound; $positionInRound++) {
                $match = new TournamentMatch($matchId, $roundId, $positionInRound);

                $this->rounds[$roundId][] = $match;
                $this->matches[] = $match;

                $matchId++;
            }

            $matchesInRound /= 2;
        }
    }

    /** @param list<Team> $teams */
    private function indexTeams(array $teams): void
    {
        $this->teamsById = [];

        foreach ($teams as $team) {
            $this->teamsById[$team->getId()] = $team;
        }
    }

    /** @param list<Team> $teams */
    private function validateTeamSeeds(array $teams): void
    {
        $seeds = [];

        foreach ($teams as $team) {
            $seed = $team->getSeed();

            if ($seed === null) {
                continue;
            }

            if (isset($seeds[$seed])) {
                throw new InvalidTeamCountException(sprintf('Duplicate seed value: %d', $seed));
            }

            $seeds[$seed] = true;
        }
    }

    private function syncMatchesFromRounds(): void
    {
        $matches = [];

        foreach ($this->rounds as $roundMatches) {
            foreach ($roundMatches as $match) {
                $matches[] = $match;
            }
        }

        $this->matches = $matches;
    }

    private static function assertValidTeamCount(int $teamCount): void
    {
        if ($teamCount < 2 || ($teamCount & ($teamCount - 1)) !== 0) {
            throw InvalidTeamCountException::notPowerOfTwo($teamCount);
        }
    }
}
