<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\CookieLocaleGuesser;
use Symfony\Component\HttpFoundation\Request;

class CookieLocaleGuesserTest extends AbstractTestGuesser
{
    public function testLocaleIsRetrievedFromCookieIfSet(): void
    {
        $request = $this->getRequest();
        $metaValidator = $this->getMockMetaValidator();

        $metaValidator->expects(self::once())
            ->method('isAllowed')
            ->with('ru')
            ->willReturn(true)
        ;

        $guesser = new CookieLocaleGuesser($metaValidator, 'human_direct_locale');

        self::assertTrue($guesser->guess($request));
        self::assertEquals('ru', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedFromCookieIfSetAndInvalid(): void
    {
        $request = $this->getRequest();
        $metaValidator = $this->getMockMetaValidator();

        $metaValidator->expects(self::once())
            ->method('isAllowed')
            ->with('ru')
            ->willReturn(false)
        ;

        $guesser = new CookieLocaleGuesser($metaValidator, 'human_direct_locale');

        self::assertFalse($guesser->guess($request));
        self::assertNull($guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedIfCookieNotSet(): void
    {
        $request = $this->getRequest(false);
        $metaValidator = $this->getMockMetaValidator();

        $metaValidator->expects(self::never())
            ->method('isAllowed')
        ;

        $guesser = new CookieLocaleGuesser($metaValidator, 'human_direct_locale');

        self::assertFalse($guesser->guess($request));
        self::assertNull($guesser->getIdentifiedLocale());
    }

    private function getRequest(bool $withLocaleCookie = true): Request
    {
        $request = Request::create('/');
        if ($withLocaleCookie) {
            $request->cookies->set('human_direct_locale', 'ru');
        }

        return $request;
    }
}
