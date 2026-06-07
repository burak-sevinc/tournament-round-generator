<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\Support;

use BurakSevinc\TournamentRoundGenerator\Support\TemplateMatchUrlGenerator;
use BurakSevinc\TournamentRoundGenerator\TournamentMatch;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies placeholder replacement behavior in TemplateMatchUrlGenerator.
 */
#[Group('support')]
#[Group('url')]
final class TemplateMatchUrlGeneratorTest extends TestCase
{
    #[Test]
    #[TestDox('Replaces {matchId} and {roundId} placeholders with match data')]
    public function it_replaces_match_and_round_placeholders(): void
    {
        $generator = new TemplateMatchUrlGenerator('/round/{roundId}/match/{matchId}');
        $match = new TournamentMatch(5, 2, 0);

        $this->assertSame('/round/2/match/5', $generator->generate($match));
    }

    #[Test]
    #[TestDox('Supports templates that use only {matchId}')]
    public function it_supports_match_id_only_templates(): void
    {
        $generator = new TemplateMatchUrlGenerator('/tournament/match/{matchId}');
        $match = new TournamentMatch(12, 3, 0);

        $this->assertSame('/tournament/match/12', $generator->generate($match));
    }

    #[Test]
    #[TestDox('Replaces every occurrence when the same placeholder appears multiple times')]
    public function it_replaces_repeated_placeholders(): void
    {
        $generator = new TemplateMatchUrlGenerator('/match/{matchId}/details/{matchId}');
        $match = new TournamentMatch(3, 1, 0);

        $this->assertSame('/match/3/details/3', $generator->generate($match));
    }
}
