<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\TopLevelDomainLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleInformation\TopLevelDomainLocaleMap;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopLevelDomainLocaleGuesserTest extends AbstractTestGuesser
{
    /**
     * @dataProvider dataDomains
     *
     * @param string|bool $mappedLocale
     */
    public function testGuessLocale(
        bool $expected,
        ?string $expectedLocale,
        string $host,
        ?bool $allowed,
        $mappedLocale
    ): void {
        $metaValidator = $this->getMockMetaValidator();
        $localeMap = $this->getMockTopLevelDomainLocaleMap();
        $localeMap
            ->method('getLocale')
            ->willReturn($mappedLocale)
        ;

        if ($allowed) {
            $metaValidator->expects(self::once())
                ->method('isAllowed')
                ->willReturn($allowed)
            ;
        }

        $request = $this->getMockRequest();
        $request->expects(self::once())
            ->method('getHost')
            ->willReturn($host)
        ;

        $guesser = new TopLevelDomainLocaleGuesser($metaValidator, $localeMap);

        self::assertEquals($expected, $guesser->guess($request));
        self::assertEquals($expectedLocale, $guesser->getIdentifiedLocale());
    }

    /**
     * @return array<int, array{bool, null|string, string, bool|null, bool|string}>
     */
    public function dataDomains(): array
    {
        return [
            [false, null, 'localhost', false, false], // no dot
            [true, 'en_GB', 'domain.co.uk', true, 'en_GB'], // double dot + sub-locale
            [true, 'de_CH', 'domain.ch', true, 'de_CH'], // single dot tld + sub-locale
            [false, null, 'domain.fr', false, false], // not allowed
            [false, null, 'domain', null, false], // no tld
            [false, null, 'domain.doom', false, false], // wrong/not allowed tld
            [true, 'de', 'domain.de', true, false], // normal tld to locale mapping
        ];
    }

    /**
     * @return MockObject&TopLevelDomainLocaleMap
     */
    private function getMockTopLevelDomainLocaleMap(): MockObject
    {
        return $this->createMock(TopLevelDomainLocaleMap::class);
    }
}
