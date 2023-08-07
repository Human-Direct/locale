<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\EventListener;

use HumanDirect\LocaleBundle\Event\FilterLocaleSwitchEvent;
use HumanDirect\LocaleBundle\EventListener\LocaleListener;
use HumanDirect\LocaleBundle\Events;
use HumanDirect\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleGuesser\CookieLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use HumanDirect\LocaleBundle\LocaleGuesser\QueryLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use HumanDirect\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use HumanDirect\LocaleBundle\Matcher\BestLocaleMatcher;
use HumanDirect\LocaleBundle\Matcher\BestLocaleMatcherInterface;
use HumanDirect\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LocaleListenerTest extends TestCase
{
    public function testDefaultLocaleWithoutParams(): void
    {
        $listener = $this->getListener('fr', $this->getGuesserManager());
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        self::assertEquals('fr', $request->getLocale());
    }

    private function getListener(
        string $locale = 'en',
        ?LocaleGuesserManager $manager = null,
        ?LoggerInterface $logger = null,
        ?BestLocaleMatcherInterface $matcher = null,
        ?EventDispatcherInterface $dispatcher = null,
        bool $disableVaryHeader = false,
        ?string $excludePattern = null
    ): LocaleListener {
        if (null === $manager) {
            $manager = $this->getGuesserManager();
        }
        if (null === $dispatcher) {
            $dispatcher = new EventDispatcher();
        }

        return new LocaleListener(
            $manager,
            $dispatcher,
            $locale,
            $disableVaryHeader,
            $excludePattern,
            $matcher,
            $logger
        );
    }

    /**
     * @param string[] $order
     */
    private function getGuesserManager(array $order = [1 => 'router', 2 => 'browser']): LocaleGuesserManager
    {
        $allowedLocales = ['de', 'fr', 'fr_FR', 'nl', 'es', 'en'];
        $metaValidator = $this->getMetaValidatorMock();
        $callBack = fn ($v): bool => \in_array($v, $allowedLocales, true);
        $metaValidator->expects(self::any())
            ->method('isAllowed')
            ->willReturnCallback($callBack)
        ;

        $manager = new LocaleGuesserManager($order);
        $routerGuesser = new RouterLocaleGuesser($metaValidator);
        $browserGuesser = new BrowserLocaleGuesser($metaValidator);
        $cookieGuesser = new CookieLocaleGuesser($metaValidator, 'human_direct_locale');
        $queryGuesser = new QueryLocaleGuesser($metaValidator, '_locale');
        $manager->addGuesser($queryGuesser, 'query');
        $manager->addGuesser($routerGuesser, 'router');
        $manager->addGuesser($browserGuesser, 'browser');
        $manager->addGuesser($cookieGuesser, 'cookie');

        return $manager;
    }

    /**
     * @return MetaValidator|MockObject
     */
    private function getMetaValidatorMock(): MetaValidator
    {
        return $this->createMock(MetaValidator::class);
    }

    private function getEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * @return array<int, array{string, array{string}, string, string}>
     */
    public function getTestDataForBestLocaleMatcher(): array
    {
        return [
            ['fr', ['fr'], 'fr', 'en'],
            ['fr_FR', ['fr'], 'fr', 'en'],
            ['fr_FR', ['fr_FR'], 'fr_FR', 'en'],
            ['fr_FR', ['en_GB'], 'en', 'en'],
        ];
    }

    /**
     * @dataProvider getTestDataForBestLocaleMatcher
     *
     * @param string[] $allowedLocales
     */
    public function testAllowedLocaleWithMatcher(
        string $browserLocale,
        array $allowedLocales,
        string $expectedLocale,
        string $fallback
    ): void {
        $listener = $this->getListener(
            $fallback,
            $this->getGuesserManager(),
            null,
            $this->getBestLocaleMatcher($allowedLocales)
        );
        $request = Request::create('/');
        $request->headers->set('Accept-language', $browserLocale);
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        self::assertEquals($expectedLocale, $request->getLocale());
    }

    /**
     * @param string[] $allowedLocales
     */
    private function getBestLocaleMatcher(array $allowedLocales): BestLocaleMatcher
    {
        return new BestLocaleMatcher(new AllowedLocalesProvider($allowedLocales));
    }

    public function testCustomLocaleIsSetWhenParamsExist(): void
    {
        $listener = $this->getListener('fr', $this->getGuesserManager());
        $request = Request::create('/', 'GET');
        $request->attributes->set('_locale', 'de');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        self::assertEquals('de', $request->getLocale());
        self::assertEquals('de', $request->attributes->get('_locale'));
    }

    public function testCustomLocaleIsSetWhenQueryExist(): void
    {
        $listener = $this->getListener('fr', $this->getGuesserManager([0 => 'router', 1 => 'query', 2 => 'browser']));
        $request = Request::create('/', 'GET');
        $request->query->set('_locale', 'de');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        self::assertEquals('de', $request->getLocale());
        self::assertEquals('de', $request->attributes->get('_locale'));
    }

    /**
     * Router is priority 1
     * Request contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testRouteLocaleIsReturnedIfRouterIsPriority1(): void
    {
        $request = $this->getFullRequest();
        $manager = $this->getGuesserManager();
        $listener = $this->getListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        self::assertEquals('es', $request->getLocale());
        self::assertEquals('es', $request->attributes->get('_locale'));
    }

    private function getFullRequest(?string $routerLocale = 'es'): Request
    {
        $request = Request::create('/');
        if (!empty($routerLocale)) {
            $request->attributes->set('_locale', $routerLocale);
        }
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    /**
     * Browser is priority 1
     * Request contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testBrowserLocaleIsReturnedIfBrowserIsPriority1(): void
    {
        $request = $this->getFullRequest();
        $manager = $this->getGuesserManager([1 => 'browser', 2 => 'router']);
        $listener = $this->getListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        self::assertEquals('fr_FR', $request->getLocale());
        self::assertEquals('fr_FR', $request->attributes->get('_locale'));
    }

    /**
     * Router is priority 1
     * Request DOES NOT contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testBrowserTakeOverIfRouterParamsFail(): void
    {
        $request = $this->getFullRequest(null);
        $manager = $this->getGuesserManager();
        $listener = $this->getListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        self::assertEquals('fr_FR', $request->getLocale());
        self::assertEquals('fr_FR', $request->attributes->get('_locale'));
    }

    public function testThatGuesserIsNotCalledIfNotInGuessingOrder(): void
    {
        $request = $this->getRequestWithRouterParam();
        $manager = $this->getGuesserManager([0 => 'browser']);
        $listener = $this->getListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        self::assertEquals('en', $request->getLocale());
    }

    private function getRequestWithRouterParam(?string $routerLocale = 'es'): Request
    {
        $request = Request::create('/');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        if (!empty($routerLocale)) {
            $request->attributes->set('_locale', $routerLocale);
        }
        $request->headers->set('Accept-language', '');

        return $request;
    }

    public function testDispatcherIsFired(): void
    {
        $dispatcherMock = $this->createMock(EventDispatcher::class);
        $dispatcherMock->expects(self::once())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(FilterLocaleSwitchEvent::class),
                self::equalTo(Events::LOCALE_CHANGE),
            )
        ;

        $listener = $this->getListener('fr', $this->getGuesserManager(), null, null, $dispatcherMock);

        $event = $this->getEvent($this->getRequestWithRouterParam());
        $listener->onKernelRequest($event);
    }

    public function testDispatcherIsNotFired(): void
    {
        $dispatcherMock = $this->createMock(EventDispatcher::class);
        $dispatcherMock->expects(self::never())
            ->method('dispatch')
        ;

        $manager = $this->getGuesserManager();
        $manager->removeGuesser('session');
        $manager->removeGuesser('cookie');
        $listener = $this->getListener('fr', $manager, null, null, $dispatcherMock);

        $event = $this->getEvent($this->getRequestWithRouterParam());
        $listener->onKernelRequest($event);
    }

    /**
     * Request with empty route params and empty browser preferences
     */
    public function testDefaultLocaleIfEmptyRequest(): void
    {
        $request = $this->getEmptyRequest();
        $manager = $this->getGuesserManager();
        $listener = $this->getListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        self::assertEquals('en', $request->getLocale());
    }

    private function getEmptyRequest(): Request
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');

        return $request;
    }

    public function testAjaxRequestsAreHandled(): void
    {
        $request = $this->getRequestWithRouterParam('fr');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $manager = $this->getGuesserManager([0 => 'router']);
        $listener = $this->getListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        self::assertEquals('fr', $request->getLocale());
    }

    public function testOnLocaleDetectedSetVaryHeader(): void
    {
        $listener = $this->getListener();

        $response = $this->getMockResponse();
        $response
            ->expects(self::once())
            ->method('setVary')
            ->with('Accept-Language')
            ->willReturn($response)
        ;

        $responseEvent = $this->getResponseEvent($response);

        self::assertEquals($listener->onKernelResponse($responseEvent), $response);
    }

    /**
     * @return MockObject|Response
     */
    private function getMockResponse()
    {
        return $this->createMock(Response::class);
    }

    private function getResponseEvent(Response $response): ResponseEvent
    {
        return new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
    }

    public function testOnLocaleDetectedDisabledVaryHeader(): void
    {
        $listener = $this->getListener('en', null, null, null, null, true);
        $response = $this->getMockResponse();
        $response
            ->expects(self::never())
            ->method('setVary')
        ;
        $responseEvent = $this->getResponseEvent($response);

        $listener->onKernelResponse($responseEvent);
    }

    /**
     * @return array<int, array{null|string, bool}>
     */
    public function excludedPatternDataProvider(): array
    {
        return [
            [null, true],
            ['.*', false],
            ['/api$', true],
            ['^/api', false],
        ];
    }

    /**
     * @dataProvider excludedPatternDataProvider
     */
    public function testGuessingIsNotFiredIfPatternMatches(?string $pattern, bool $called): void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/users']);

        $guesserManager = $this->getMockGuesserManager();
        $guesserManager
            ->expects(self::exactly((int) $called))
            ->method('guess')
        ;

        $listener = $this->getListener('en', $guesserManager, null, null, null, false, $pattern);
        $listener->onKernelRequest($this->getEvent($request));
    }

    /**
     * @return LocaleGuesserManager|MockObject
     */
    private function getMockGuesserManager()
    {
        return $this->createMock(LocaleGuesserManager::class);
    }

    public function testLogEvent(): void
    {
        $request = $this->getEmptyRequest();

        $guesserManager = $this->getMockGuesserManager();
        $guesserManager
            ->expects(self::once())
            ->method('guess')
            ->with($request)
            ->willReturn('hu')
        ;

        $logger = $this->getMockLogger();
        $logger
            ->expects(self::once())
            ->method('info')
            ->with('Setting [ hu ] as locale for the (Sub-)Request', [])
        ;

        $listener = $this->getListener('en', $guesserManager, $logger);
        $listener->onKernelRequest($this->getEvent($request));
    }

    /**
     * @return MockObject|LoggerInterface
     */
    private function getMockLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = LocaleListener::getSubscribedEvents();

        self::assertEquals([['onKernelRequest', 24]], $subscribedEvents[KernelEvents::REQUEST]);
        self::assertEquals(['onKernelResponse'], $subscribedEvents[KernelEvents::RESPONSE]);
    }
}
