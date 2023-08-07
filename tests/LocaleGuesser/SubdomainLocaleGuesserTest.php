<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\SubdomainLocaleGuesser;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class SubdomainLocaleGuesserTest extends AbstractTestGuesser
{
    /**
     * @dataProvider dataDomains
     */
    public function testGuessLocale(
        bool $expected,
        string $host,
        ?bool $allowed,
        ?string $separator
    ): void {
        $metaValidator = $this->getMockMetaValidator();

        if (null !== $allowed) {
            $metaValidator
                ->expects(self::once())
                ->method('isAllowed')
                ->willReturn($allowed)
            ;
        }

        $request = $this->getMockRequest();
        $request
            ->expects(self::once())
            ->method('getHost')
            ->willReturn($host)
        ;

        $guesser = new SubdomainLocaleGuesser($metaValidator, $separator);

        self::assertEquals($expected, $guesser->guess($request));
    }

    /**
     * @return array{bool, string, bool|null, null|string}[]
     */
    public function dataDomains(): array
    {
        return [
            [true, 'en.domain', true, null],
            [false, 'fr.domain', false, null],
            [false, 'domain', null, null],
            [false, 'www.domain', false, null],
            [true, 'en-ca.domain', true, '-'],
            [true, 'fr_ca.domain', true, '_'],
            [false, 'de-DE.domain', false, '_'],
        ];
    }
}
