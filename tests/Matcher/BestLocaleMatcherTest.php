<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\EventListener;

use HumanDirect\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use HumanDirect\LocaleBundle\Matcher\BestLocaleMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class BestLocaleMatcherTest extends TestCase
{
    /**
     * @dataProvider getTestDataForBestLocaleMatcher
     *
     * @param string[] $allowed
     * @param string|bool $expected
     */
    public function testMatch(string $locale, array $allowed, $expected): void
    {
        $matcher = new BestLocaleMatcher(new AllowedLocalesProvider($allowed));

        self::assertEquals($expected, $matcher->match($locale));
    }

    /**
     * @return array<int, array{string, string[], string|bool}>
     */
    public function getTestDataForBestLocaleMatcher(): array
    {
        return [
            ['fr', ['fr'], 'fr'],
            ['fr_FR', ['fr', 'fr_FR'], 'fr_FR'],
            ['fr_FR', ['fr_FR', 'fr'], 'fr_FR'],
            ['fr_FR', ['fr'], 'fr'],
            ['fr_FR', ['fr_FR'], 'fr_FR'],
            ['fr_FR', ['en_GB'], false],
        ];
    }
}
