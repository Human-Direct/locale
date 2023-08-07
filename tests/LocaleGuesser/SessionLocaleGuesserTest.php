<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\Tests\LocaleGuesser;

use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use HumanDirect\LocaleBundle\LocaleGuesser\SessionLocaleGuesser;
use HumanDirect\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\Builder\InvocationStubber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SessionLocaleGuesserTest extends AbstractTestGuesser
{
    public function testGuesserExtendsInterface(): void
    {
        $request = $this->getRequestWithSessionLocale();
        $guesser = $this->getGuesser($request->getSession(), $this->getMockMetaValidator());
        self::assertInstanceOf(LocaleGuesserInterface::class, $guesser);
    }

    public function testGuessLocaleWithoutSessionVariable(): void
    {
        $request = $this->getRequestWithSessionLocale();
        $guesser = $this->getGuesser();

        self::assertFalse($guesser->guess($request));
    }

    public function testLocaleIsRetrievedFromSessionIfSet(): void
    {
        $request = $this->getRequestWithSessionLocale();
        $metaValidator = $this->getMockMetaValidator();
        $inputs = ['ru'];
        $outputs = [true];

        $expectation = $metaValidator->expects(self::once())
            ->method('isAllowed')
        ;
        $this->setMultipleMatching($expectation, $inputs, $outputs);

        $guesser = $this->getGuesser($request->getSession(), $metaValidator);
        $guesser->guess($request);

        self::assertEquals('ru', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedFromSessionIfInvalid(): void
    {
        $request = $this->getRequestWithSessionLocale();
        $metaValidator = $this->getMockMetaValidator();

        $expectation = $metaValidator->expects(self::once())
            ->method('isAllowed')
        ;
        $this->setMultipleMatching($expectation, ['ru'], [false]);

        $guesser = $this->getGuesser($request->getSession(), $metaValidator);
        $guesser->guess($request);

        self::assertNull($guesser->getIdentifiedLocale());
    }

    public function testSetSessionLocale(): void
    {
        $locale = uniqid('locale:');

        $guesser = $this->getGuesser();
        $guesser->setSessionLocale($locale, true);

        self::assertEquals($locale, $guesser->getSessionLocale());
    }

    public function testLocaleIsNotRetrievedFromSessionIfNotStarted(): void
    {
        $request = $this->getRequestNoSessionLocale();
        $metaValidator = $this->getMockMetaValidator();
        $expectation = $metaValidator->expects(self::never())
            ->method('isAllowed')
        ;
        $this->setMultipleMatching($expectation, ['ru'], [false]);

        $guesser = $this->getGuesser($request->getSession(), $metaValidator);
        $guesser->guess($request);

        self::assertNull($guesser->getIdentifiedLocale());
    }

    public function testSessionIsNotAutomaticallyStarted(): void
    {
        $request = $this->getRequestNoSessionLocale();
        $metaValidator = $this->getMockMetaValidator();
        $session = $request->getSession();

        $guesser = $this->getGuesser($request->getSession(), $metaValidator);
        $guesser->guess($request);

        self::assertFalse($session->isStarted());
    }

    private function getGuesser(
        ?SessionInterface $session = null,
        ?MetaValidator $metaValidator = null
    ): SessionLocaleGuesser {
        if (null === $session) {
            $session = $this->getSession();
        }

        if (null === $metaValidator) {
            $metaValidator = $this->getMockMetaValidator();
        }

        $request = Request::create('/');
        $request->setSession($session);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new SessionLocaleGuesser($requestStack, $metaValidator);
    }

    private function getRequestNoSessionLocale(): Request
    {
        $session = $this->getSession();
        $request = Request::create('/');
        $request->setSession($session);
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRequestWithSessionLocale(?string $locale = 'ru'): Request
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('human_direct_locale', $locale);

        $request = Request::create('/');
        $request->setSession($session);
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getSession(): SessionInterface
    {
        return new Session(new MockArraySessionStorage());
    }

    /**
     * A callback is built and linked to the mocked method.
     *
     * @param array<int, mixed> $inputs
     * @param array<int, mixed> $outputs
     */
    public function setMultipleMatching(
        InvocationStubber $expectation,
        array $inputs,
        array $outputs
    ): void {
        $callback = function () use ($inputs, $outputs) {
            $args = \func_get_args();
            $this->assertContains($args[0], $inputs);
            $index = array_search($args[0], $inputs, true);

            return $outputs[$index];
        };

        $expectation->willReturnCallback($callback);
    }
}
