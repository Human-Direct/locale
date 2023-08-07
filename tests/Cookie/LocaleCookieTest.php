<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\DependencyInjection;

use HumanDirect\LocaleBundle\Cookie\LocaleCookie;
use PHPUnit\Framework\TestCase;

class LocaleCookieTest extends TestCase
{
    public function testCookieParamsAreSet(): void
    {
        $localeCookie = new LocaleCookie('human_direct_locale', 86400, '/', null, false, true, true);
        $cookie = $localeCookie->getLocaleCookie('en');
        self::assertEquals('human_direct_locale', $cookie->getName());
        self::assertEquals('en', $cookie->getValue());
        self::assertEquals('/', $cookie->getPath());
        self::assertEquals(null, $cookie->getDomain());
        self::assertTrue($cookie->isHttpOnly());
        self::assertFalse($cookie->isSecure());
    }

    public function testCookieExpiresDateTime(): void
    {
        $localeCookie = new LocaleCookie('human_direct_locale', 86400, '/', null, false, true, true);
        $cookie = $localeCookie->getLocaleCookie('en');
        self::assertTrue($cookie->getExpiresTime() > time());
        self::assertTrue($cookie->getExpiresTime() <= (time() + 86400));
    }
}
