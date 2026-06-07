<?php

declare(strict_types=1);

namespace BurakSevinc\TournamentRoundGenerator\Tests\SingleElimination;

use BurakSevinc\TournamentRoundGenerator\SingleElimination;
use BurakSevinc\TournamentRoundGenerator\Support\TemplateMatchUrlGenerator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Verifies match URL generation and applyMatchUrls behavior.
 */
#[Group('single-elimination')]
#[Group('url')]
final class MatchUrlTest extends TestCase
{
    #[Test]
    #[TestDox('applyMatchUrls assigns template-based URLs to all matches')]
    public function it_applies_template_urls_to_all_matches(): void
    {
        $bracket = (new SingleElimination(8))
            ->setMatchUrlGenerator(new TemplateMatchUrlGenerator('/tournament/match/{matchId}'))
            ->applyMatchUrls();

        $this->assertSame('/tournament/match/1', $bracket->getMatchById(1)?->getUrl());
        $this->assertSame('/tournament/match/7', $bracket->getMatchById(7)?->getUrl());
    }

    #[Test]
    #[TestDox('Includes generated URLs in the getMatches array output')]
    public function it_includes_url_in_match_array_output(): void
    {
        $bracket = (new SingleElimination(4))
            ->setMatchUrlGenerator(new TemplateMatchUrlGenerator('/round/{roundId}/match/{matchId}'))
            ->applyMatchUrls();

        $firstMatch = $bracket->getMatches()[0];

        $this->assertSame('/round/1/match/1', $firstMatch['url']);
    }

    #[Test]
    #[TestDox('Leaves match URLs unchanged when applyMatchUrls is called without a generator')]
    public function it_skips_url_assignment_when_no_generator_is_configured(): void
    {
        $bracket = (new SingleElimination(2))->applyMatchUrls();

        $this->assertNull($bracket->getMatchById(1)?->getUrl());
    }
}
