<?php

declare(strict_types = 1);

namespace HumanDirect\LocaleBundle\EventListener;

use HumanDirect\LocaleBundle\Event\FilterLocaleSwitchEvent;
use HumanDirect\LocaleBundle\Events;
use HumanDirect\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use HumanDirect\LocaleBundle\Matcher\BestLocaleMatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LocaleGuesserManager $guesserManager,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly string $defaultKernelLocale,
        private readonly bool $disableVaryHeader,
        private readonly ?string $guessingExcludedPattern,
        private readonly ?BestLocaleMatcherInterface $bestLocaleMatcher = null,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Sets the identified locale as default locale to the request.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($this->guessingExcludedPattern
            && preg_match(sprintf('#%s#', $this->guessingExcludedPattern), $request->getPathInfo())) {
            return;
        }

        $request->setDefaultLocale($this->defaultKernelLocale);

        $locale = $this->guesserManager->guess($request);
        if ($locale && $this->bestLocaleMatcher) {
            $locale = $this->bestLocaleMatcher->match($locale);
        }

        if (!\is_string($locale) || empty($locale)) {
            return;
        }

        $this->log($locale);
        $request->setLocale($locale);
        $request->attributes->set('_locale', $locale);

        if ((HttpKernelInterface::MAIN_REQUEST === $event->getRequestType() || $request->isXmlHttpRequest())
            && ($this->guesserManager->getGuesser('session') || $this->guesserManager->getGuesser('cookie'))) {
            $localeSwitchEvent = new FilterLocaleSwitchEvent($request, $locale);
            $this->dispatcher->dispatch($localeSwitchEvent, Events::LOCALE_CHANGE);
        }
    }

    /**
     * This Listener adds a Vary header to all responses.
     */
    public function onKernelResponse(ResponseEvent $event): Response
    {
        $response = $event->getResponse();
        if (!$this->disableVaryHeader) {
            $response->setVary('Accept-Language', false);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, array{string}|array{array{string,int}}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered after the Router to have access to the _locale and before the Symfony LocaleListener
            KernelEvents::REQUEST => [['onKernelRequest', 24]],
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }

    private function log(bool|float|int|string|null $parameters = null): void
    {
        $this->logger?->info(sprintf('Setting [ %s ] as locale for the (Sub-)Request', $parameters));
    }
}
