<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\EventListener;

use HumanDirect\LocaleBundle\Cookie\LocaleCookie;
use HumanDirect\LocaleBundle\Event\FilterLocaleSwitchEvent;
use HumanDirect\LocaleBundle\Events;
use HumanDirect\LocaleBundle\Session\LocaleSession;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Locale Update Listener
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleUpdateListener implements EventSubscriberInterface
{
    private string $locale = '';

    /**
     * @param string[] $guessingOrder
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LocaleCookie $localeCookie,
        private readonly ?LocaleSession $session = null,
        private readonly array $guessingOrder = [],
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Processes the locale updates. Adds listener for the cookie and updates the session.
     */
    public function onLocaleChange(FilterLocaleSwitchEvent $event): void
    {
        $this->locale = $event->getLocale();
        $this->updateCookie($event->getRequest(), $this->localeCookie->setCookieOnChange());
        $this->updateSession();
    }

    public function updateCookie(Request $request, bool $update = false): bool
    {
        if (!$update
            || !$this->isGuesserRegistered('cookie')
            || $this->getCookieLocale($request) === $this->locale) {
            return false;
        }

        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$this, 'updateCookieOnResponse']);

        return true;
    }

    public function updateCookieOnResponse(ResponseEvent $event): Response
    {
        $response = $event->getResponse();
        $cookie = $this->localeCookie->getLocaleCookie($this->locale);
        $response->headers->setCookie($cookie);

        if ($this->logger) {
            $this->logger->info(sprintf('Locale Cookie set to [ %s ]', $this->locale));
        }

        return $response;
    }

    public function updateSession(): bool
    {
        if (!$this->session
            || !$this->isGuesserRegistered('session')
            || !$this->session->hasLocaleChanged($this->locale)) {
            return false;
        }

        if ($this->logger) {
            $key = $this->session->getSessionKey();
            $this->logger->info(sprintf('Session key "%s" set to [ %s ]', $key, $this->locale));
        }
        $this->session->setLocale($this->locale);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, array{string}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered after the Router to have access to the _locale and before the Symfony LocaleListener
            Events::LOCALE_CHANGE => ['onLocaleChange'],
        ];
    }

    private function isGuesserRegistered(string $guesser): bool
    {
        return \in_array($guesser, $this->guessingOrder, true);
    }

    private function getCookieLocale(Request $request): ?string
    {
        return $request->cookies->get($this->localeCookie->getName());
    }
}
