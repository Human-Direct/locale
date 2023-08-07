<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\EventListener;

use HumanDirect\LocaleBundle\Cookie\LocaleCookie;
use HumanDirect\LocaleBundle\Event\FilterLocaleSwitchEvent;
use HumanDirect\LocaleBundle\EventListener\LocaleUpdateListener;
use HumanDirect\LocaleBundle\Events;
use HumanDirect\LocaleBundle\Session\LocaleSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Webmozart\Assert\Assert;

class LocaleUpdateTest extends TestCase
{
    private EventDispatcher $dispatcher;
    private LocaleSession $session;

    protected function setUp(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn(new Session(new MockArraySessionStorage()));

        $this->dispatcher = new EventDispatcher();
        $this->session = new LocaleSession($requestStack);
    }

    public function testCookieIsNotUpdatedNoGuesser(): void
    {
        $request = $this->getRequest(false);
        $listener = $this->getLocaleUpdateListener(['session'], true);

        self::assertFalse($listener->updateCookie($request, true));
        self::assertFalse($listener->updateCookie($request, false));

        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);

        self::assertSame([], $addedListeners);
    }

    public function testCookieIsNotUpdatedOnSameLocale(): void
    {
        $listener = $this->getLocaleUpdateListener(['cookie'], true);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(true, 'de'));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        self::assertSame([], $addedListeners);
    }

    public function testCookieIsUpdatedOnChange(): void
    {
        $listener = $this->getLocaleUpdateListener(['cookie'], true);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);

        Assert::isIterable($addedListeners[0]);
        self::assertContains('updateCookieOnResponse', $addedListeners[0]);
    }

    public function testCookieIsNotUpdatedWithFalseSetCookieOnChange(): void
    {
        $listener = $this->getLocaleUpdateListener(['cookie'], false);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        self::assertSame([], $addedListeners);
    }

    public function testUpdateCookieOnResponse(): void
    {
        $event = $this->getEvent($this->getRequest());

        $logger = $this->getMockLogger();
        $logger
            ->expects(self::once())
            ->method('info')
            ->with('Locale Cookie set to [ es ]')
        ;

        $listener = $this->getLocaleUpdateListener([], false, $logger);

        $reflectionClass = new \ReflectionClass($listener);
        $property = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'es');

        $response = $listener->updateCookieOnResponse($event);

        [$cookie] = $response->headers->getCookies();
        self::assertInstanceOf(Cookie::class, $cookie);
        self::assertEquals('human_direct_locale', $cookie->getName());
        self::assertEquals('es', $cookie->getValue());
    }

    public function testUpdateSession(): void
    {
        $this->session->setLocale('el');

        $logger = $this->getMockLogger();
        $logger
            ->expects(self::once())
            ->method('info')
            ->with('Session key "human_direct_locale" set to [ tr ]')
        ;

        $listener = $this->getLocaleUpdateListener(['session'], false, $logger);

        $reflectionClass = new \ReflectionClass($listener);
        $property = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'tr');

        self::assertTrue($listener->updateSession());
    }

    public function testNotUpdateSessionNoGuesser(): void
    {
        $this->session->setLocale('el');
        $listener = $this->getLocaleUpdateListener(['cookie']);

        $reflectionClass = new \ReflectionClass($listener);
        $property = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'el');

        self::assertFalse($listener->updateSession());
    }

    public function testNotUpdateSessionSameLocale(): void
    {
        $this->session->setLocale('el');
        $listener = $this->getLocaleUpdateListener(['session']);

        $reflectionClass = new \ReflectionClass($listener);
        $property = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'el');

        self::assertFalse($listener->updateSession());
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = LocaleUpdateListener::getSubscribedEvents();

        self::assertEquals(['onLocaleChange'], $subscribedEvents[Events::LOCALE_CHANGE]);
    }

    private function getFilterLocaleSwitchEvent(
        bool $withCookieSet = true,
        string $locale = 'fr'
    ): FilterLocaleSwitchEvent {
        return new FilterLocaleSwitchEvent($this->getRequest($withCookieSet), $locale);
    }

    /**
     * @param string[] $registeredGuessers
     */
    private function getLocaleUpdateListener(
        array $registeredGuessers = [],
        bool $updateCookie = false,
        ?LoggerInterface $logger = null
    ): LocaleUpdateListener {
        return new LocaleUpdateListener(
            $this->dispatcher,
            $this->getLocaleCookie($updateCookie),
            $this->session,
            $registeredGuessers,
            $logger
        );
    }

    private function getEvent(Request $request): ResponseEvent
    {
        return new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );
    }

    private function getLocaleCookie(bool $updateCookie): LocaleCookie
    {
        return new LocaleCookie('human_direct_locale', 86400, '/', null, false, true, $updateCookie);
    }

    private function getRequest(bool $withCookieSet = false): Request
    {
        return Request::create('/', 'GET', [], $withCookieSet ? ['human_direct_locale' => 'de'] : []);
    }

    /**
     * @return MockObject&LoggerInterface
     */
    private function getMockLogger(): MockObject
    {
        return $this->createMock(LoggerInterface::class);
    }
}
