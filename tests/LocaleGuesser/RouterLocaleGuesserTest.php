<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use HumanDirect\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Symfony\Component\HttpFoundation\Request;

class RouterLocaleGuesserTest extends AbstractTestGuesser
{
    public function testGuesserExtendsInterface(): void
    {
        $guesser = new RouterLocaleGuesser($this->getMockMetaValidator());
        self::assertInstanceOf(LocaleGuesserInterface::class, $guesser);
    }

    public function testLocaleIsIdentified(): void
    {
        $request = $this->getRequestWithLocaleAttribute();
        $metaValidator = $this->getMockMetaValidator();
        $guesser = new RouterLocaleGuesser($metaValidator);

        $metaValidator->expects(self::once())
            ->method('isAllowed')
            ->with('en')
            ->willReturn(true)
        ;

        self::assertTrue($guesser->guess($request));
        self::assertEquals('en', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotAllowed(): void
    {
        $request = $this->getRequestWithLocaleAttribute();
        $metaValidator = $this->getMockMetaValidator();
        $guesser = new RouterLocaleGuesser($metaValidator);

        $metaValidator->expects(self::once())
            ->method('isAllowed')
            ->with('en')
            ->willReturn(false)
        ;

        self::assertFalse($guesser->guess($request));
        self::assertNull($guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentified(): void
    {
        $request = $this->getRequestWithLocaleQuery('fr');
        $metaValidator = $this->getMockMetaValidator();
        $guesser = new RouterLocaleGuesser($metaValidator);

        $metaValidator->expects(self::never())
            ->method('isAllowed')
        ;

        $guesser->guess($request);
        self::assertEquals(false, $guesser->getIdentifiedLocale());
    }

    private function getRequestWithLocaleAttribute(?string $locale = 'en'): Request
    {
        $request = Request::create('/hello-world', 'GET');
        $request->attributes->set('_locale', $locale);

        return $request;
    }
}
