<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleInformation;

use HumanDirect\LocaleBundle\Session\LocaleSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSessionTest extends TestCase
{
    public function testHasLocaleChanged(): void
    {
        $localeEn = uniqid('en:');
        $localeFr = uniqid('fr:');

        $session = $this->getMockSession();
        $session
            ->expects(self::exactly(2))
            ->method('get')
            ->with('human_direct_locale')
            ->willReturnOnConsecutiveCalls(
                $localeEn,
                $localeFr
            )
        ;
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        $localeSession = new LocaleSession($requestStack);

        self::assertFalse($localeSession->hasLocaleChanged($localeEn));
        self::assertTrue($localeSession->hasLocaleChanged($localeEn));
    }

    public function testSetGetLocale(): void
    {
        $locale = uniqid('locale:');

        $session = $this->getMockSession();
        $session
            ->expects(self::once())
            ->method('set')
            ->with('human_direct_locale', $locale)
        ;
        $session
            ->expects(self::once())
            ->method('get')
            ->with('human_direct_locale')
            ->willReturn($locale)
        ;
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        $localeSession = new LocaleSession($requestStack);

        $localeSession->setLocale($locale);
        self::assertEquals($locale, $localeSession->getLocale($locale));
    }

    public function testGetSessionVar(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->createMock(SessionInterface::class));
        $localeSession = new LocaleSession($requestStack);

        self::assertEquals('human_direct_locale', $localeSession->getSessionKey());
    }

    /**
     * @return MockObject&SessionInterface
     */
    private function getMockSession(): MockObject
    {
        return $this->createMock(SessionInterface::class);
    }
}
