<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleInformation;

use HumanDirect\LocaleBundle\LocaleInformation\TopLevelDomainLocaleMap;
use PHPUnit\Framework\TestCase;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopLevelDomainLocaleMapTest extends TestCase
{
    public function testGetLocale(): void
    {
        $tldLocaleMap = new TopLevelDomainLocaleMap([
            'net' => 'de',
            'org' => null,
            'com' => 'en_US',
            'uk' => 'en_GB',
            'be' => 'fr_BE',
        ]);

        self::assertEquals('en_GB', $tldLocaleMap->getLocale('uk'));
        self::assertEquals('en_US', $tldLocaleMap->getLocale('com'));
        self::assertEquals('de', $tldLocaleMap->getLocale('net'));
        self::assertEquals(false, $tldLocaleMap->getLocale('fr'));
        self::assertEquals(false, $tldLocaleMap->getLocale('org'));
        self::assertEquals('fr_BE', $tldLocaleMap->getLocale('be'));
    }
}
