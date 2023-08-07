<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\Switcher;

use HumanDirect\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use HumanDirect\LocaleBundle\Switcher\TargetInformationBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class TargetInformationBuilderTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string[]}>
     */
    public function getLocales(): array
    {
        return [
            'set 1' => ['/hello-world/', 'de', ['de', 'en', 'fr']],
            'set 2' => ['/', 'de_DE', ['de', 'en', 'fr', 'nl']],
            'set 3' => ['/test/', 'de', ['de', 'fr_FR', 'es_ES', 'nl']],
            'set 4' => ['/foo', 'de', ['de', 'en']],
            'set 5' => ['/foo', 'de', ['de']],
            'set 6' => ['/', 'de_DE', ['de_DE', 'en', 'fr', 'nl']],
        ];
    }

    /**
     * @dataProvider getLocales
     *
     * @param string[] $allowedLocales
     */
    public function testProvideRouteInInformationBuilder(
        string $route,
        string $locale,
        array $allowedLocales
    ): void {
        $request = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();
        $count = \count($allowedLocales) - 1;
        if ($count >= 1) {
            $router->expects(self::exactly($count))
                ->method('generate')
                ->with(self::equalTo('route_foo'), self::anything())
                ->willReturn($route . '_generated')
            ;
        } else {
            $router->expects(self::never())
                ->method('generate')
            ;
        }

        $targetInformationBuilder = new TargetInformationBuilder(
            $request,
            $router,
            new AllowedLocalesProvider($allowedLocales)
        );
        $targetInformation = $targetInformationBuilder->getTargetInformation('route_foo');

        self::assertEquals($route, $targetInformation['current_route']);
        foreach ($allowedLocales as $check) {
            if (!str_starts_with($locale, $check)) {
                self::assertEquals($route . '_generated', $targetInformation['locales'][$check]['link']);
            }
        }
    }

    /**
     * @dataProvider getLocales
     *
     * @param string[] $allowedLocales
     */
    public function testNotProvideRouteInInformationBuilderNoRouter(
        string $route,
        string $locale,
        array $allowedLocales
    ): void {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();
        $count = \count($allowedLocales) - 1;
        if ($count >= 1) {
            $router->expects(self::exactly($count))
                ->method('generate')
                ->with(self::equalTo($route), self::anything())
                ->willReturn($route . '_generated')
            ;
        } else {
            $router->expects(self::never())
                ->method('generate')
            ;
        }

        $targetInformationBuilder = new TargetInformationBuilder(
            $requestStack,
            $router,
            new AllowedLocalesProvider($allowedLocales),
            false
        );
        $targetInformation = $targetInformationBuilder->getTargetInformation();

        self::assertEquals($route, $targetInformation['current_route']);
        foreach ($allowedLocales as $check) {
            if (!str_starts_with($locale, $check)) {
                self::assertEquals($route . '_generated', $targetInformation['locales'][$check]['link']);
            }
        }
    }

    /**
     * @dataProvider getLocales
     *
     * @param string[] $allowedLocales
     */
    public function testInformationBuilder(
        string $route,
        string $locale,
        array $allowedLocales
    ): void {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder(
            $requestStack,
            $router,
            new AllowedLocalesProvider($allowedLocales)
        );
        $targetInformation = $targetInformationBuilder->getTargetInformation();

        self::assertEquals($locale, $targetInformation['current_locale']);

        $count = \count($allowedLocales) - 1;
        if ($count >= 1) {
            self::assertCount($count, $targetInformation['locales']);
        } else {
            self::assertCount(0, $targetInformation['locales']);
        }
    }

    /**
     * @dataProvider getLocales
     *
     * @param string[] $allowedLocales
     */
    public function testInformationBuilderWithParams(
        string $route,
        string $locale,
        array $allowedLocales
    ): void {
        $request = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder(
            $request,
            $router,
            new AllowedLocalesProvider($allowedLocales),
            false
        );

        if (\count($allowedLocales) > 1) {
            $router->expects(self::atLeastOnce())
                ->method('generate')
                ->with(self::equalTo($route), self::arrayHasKey('foo'))
            ;
        } else {
            $router->expects(self::never())
                ->method('generate')
                ->with(self::equalTo($route), self::arrayHasKey('foo'))
            ;
        }

        $targetInformationBuilder->getTargetInformation(null, ['foo' => 'bar']);
    }

    /**
     * @dataProvider getLocales
     *
     * @param string[] $allowedLocales
     */
    public function testShowCurrentLocale(
        string $route,
        string $locale,
        array $allowedLocales
    ): void {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder(
            $requestStack,
            $router,
            new AllowedLocalesProvider($allowedLocales),
            true
        );
        $targetInformation = $targetInformationBuilder->getTargetInformation();

        self::assertEquals($locale, $targetInformation['current_locale']);

        self::assertCount(\count($allowedLocales), $targetInformation['locales']);
        foreach ($allowedLocales as $allowed) {
            self::assertArrayHasKey($allowed, $targetInformation['locales']);
        }
    }

    public function testGenerateNotCalledIfNoRoute(): void
    {
        $requestStack = $this->getRequestWithBrowserPreferences('/', '', ['_route' => null]);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder(
            $requestStack,
            $router,
            new AllowedLocalesProvider(['de', 'en', 'fr']),
            true
        );
        $router
            ->expects(self::never())
            ->method('generate')
        ;

        $targetInformationBuilder->getTargetInformation();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function getRequestWithBrowserPreferences(
        string $route = '/',
        string $locale = '',
        array $attributes = []
    ): RequestStack {
        $request = Request::create($route, Request::METHOD_GET, $attributes);
        $requestStack = new RequestStack();
        $request->setLocale($locale);
        foreach ($attributes as $key => $value) {
            $request->attributes->set($key, $value);
        }
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');
        $requestStack->push($request);

        return $requestStack;
    }

    /**
     * @return RouterInterface|MockObject
     */
    private function getRouter()
    {
        return $this
            ->getMockBuilder(RouterInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
