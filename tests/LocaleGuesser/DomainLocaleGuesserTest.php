<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\DomainLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleInformation\DomainLocaleMap;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Jachim Coudenys <jachimcoudenys@gmail.com>
 */
class DomainLocaleGuesserTest extends AbstractTestGuesser
{
    /**
     * @dataProvider dataDomains
     */
    public function testGuessLocale(
        bool $expected,
        ?string $expectedLocale,
        string $host,
        bool $allowed,
        ?string $mappedLocale
    ): void {
        $metaValidator = $this->getMockMetaValidator();
        $localeMap = $this->getMockDomainLocaleMap();
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

        $guesser = new DomainLocaleGuesser($metaValidator, $localeMap);

        self::assertEquals($expected, $guesser->guess($request));
        self::assertEquals($expectedLocale, $guesser->getIdentifiedLocale());
    }

    /**
     * @return array<int, array{bool, null|string, string, bool, string|null}>
     */
    public function dataDomains(): array
    {
        return [
            [false, null, 'localhost', false, null],
            [true, 'nl_BE', 'dutchversion.be', true, 'nl_BE'],
            [true, 'en_BE', 'sub.dutchversion.be', true, 'en_BE'],
            [true, 'fr_BE', 'frenchversion.be', true, 'fr_BE'],
            [true, 'fr_BE', 'test.frenchversion.be', true, 'fr_BE'],
            [true, 'de_CH', 'domain.ch', true, 'de_CH'],
        ];
    }

    /**
     * @return DomainLocaleMap|MockObject
     */
    private function getMockDomainLocaleMap()
    {
        return $this->createMock(DomainLocaleMap::class);
    }
}
