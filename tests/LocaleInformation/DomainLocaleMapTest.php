<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleInformation;

use HumanDirect\LocaleBundle\LocaleInformation\DomainLocaleMap;
use PHPUnit\Framework\TestCase;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class DomainLocaleMapTest extends TestCase
{
    public function testGetLocale(): void
    {
        $domainLocaleMap = new DomainLocaleMap([
            'sub.dutchversion.be' => 'en_BE',
            'dutchversion.be' => 'nl_BE',
            'spanishversion.be' => null,
            'frenchversion.be' => 'fr_BE',
        ]);

        self::assertEquals('en_BE', $domainLocaleMap->getLocale('sub.dutchversion.be'));
        self::assertEquals('nl_BE', $domainLocaleMap->getLocale('dutchversion.be'));
        self::assertEquals(false, $domainLocaleMap->getLocale('spanishversion.be'));
        self::assertEquals(false, $domainLocaleMap->getLocale('unknown.be'));
        self::assertEquals('fr_BE', $domainLocaleMap->getLocale('frenchversion.be'));
    }
}
