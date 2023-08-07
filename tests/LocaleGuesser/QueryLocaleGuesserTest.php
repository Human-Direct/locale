<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use HumanDirect\LocaleBundle\LocaleGuesser\QueryLocaleGuesser;

class QueryLocaleGuesserTest extends AbstractTestGuesser
{
    public function testGuesserExtendsInterface(): void
    {
        $guesser = new QueryLocaleGuesser($this->getMockMetaValidator());
        self::assertInstanceOf(LocaleGuesserInterface::class, $guesser);
    }

    public function testLocaleIsIdentifiedFromRequestQuery(): void
    {
        $request = $this->getRequestWithLocaleQuery();
        $metaValidator = $this->getMockMetaValidator();
        $guesser = new QueryLocaleGuesser($metaValidator);

        $metaValidator->expects(self::once())
            ->method('isAllowed')
            ->with('en')
            ->willReturn(true)
        ;

        self::assertTrue($guesser->guess($request));
        self::assertEquals('en', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedLocaleGuesserManagerTestFromRequestQuery(): void
    {
        $request = $this->getRequestWithLocaleQuery();
        $metaValidator = $this->getMockMetaValidator();
        $guesser = new QueryLocaleGuesser($metaValidator);

        $metaValidator->expects(self::once())
            ->method('isAllowed')
            ->with('en')
            ->willReturn(false)
        ;

        self::assertFalse($guesser->guess($request));
        self::assertNull($guesser->getIdentifiedLocale());
    }
}
